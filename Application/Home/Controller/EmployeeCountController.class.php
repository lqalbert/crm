<?php
namespace Home\Controller;

class EmployeeCountController  extends CommonController{

    protected $table="";
    protected $pageSize = 30;


    public function index(){
        $this->setDepartments();
        $this->assign('departments', $this->departments);
        $this->display();
    }

    public function getList(){

        $this->stDate = I('get.start')." 00:00:00";
        $this->enDate = Date('Y-m-d H:i:s', strtotime(I('get.end')." 00:00:00")+86400) ;
        $this->setQuery();
        $re = array();
        foreach ($this->departments as $key => $value) {
            $tmp = array(
                'id'=>$value['id'],
                'name'=>$value['name'],
                'all'=> isset($this->allData[$value['id']]) ? $this->allData[$value['id']] : 0,
                'add'=> isset($this->addData[$value['id']]) ? $this->addData[$value['id']] : 0,
                'dim'=> isset($this->dimData[$value['id']]) ? $this->dimData[$value['id']] : 0,
            );
            $tmp['dimall'] = round($tmp['dim']/$tmp['all'],2);

            $re[] = $tmp;
        }

        $result = array('list'=>$re, 'count'=>count($re));
        $this->ajaxReturn($result);
    }

    public function setQuery(){
        $this->setAll();
        $this->setAdd();
        $this->setDimmision();
        $this->setDepartments();
    }

    /**
    * 设置总员工数
    *  思路 rbac_user.created_at < $this->enDate 
    *  减去 在 stDate 之前离职的
    */
    private function setAll(){
        $sql = "select count(user_id) as c, department_id from rbac_user inner join user_info on rbac_user.id = user_info.user_id where rbac_user.created_at < '" .$this->enDate."'  group by  user_info.department_id";
        $re = M()->query($sql);
        $this->allData = arr_to_map($re, 'department_id', 'c');  

        $sql = "select count(user_id) as c, department_id from rbac_user inner join user_info on rbac_user.id = user_info.user_id where user_info.dimission_at < '" .$this->stDate."'  group by  user_info.department_id";
        $re2 = M()->query($sql);
        foreach ($re2 as $key => $value) {
            if (isset($this->allData[$value['department_id']])) {
                $this->allData[$value['department_id']] = $this->allData[$value['department_id']] - $value['c'];
            }
        }
    }


    /**
    * 设置入职数 这一段时间段之内的
    * 
    * 思路 rbac_user.created_at >= $this->stDate and rbac.created_at < $this->enDate
    */
    private function setAdd(){
        $sql = "select count(user_id) as c, department_id from rbac_user inner join user_info on rbac_user.id = user_info.user_id where rbac_user.created_at >= '" .$this->stDate."' and rbac_user.created_at< '".$this->enDate."'  group by  user_info.department_id";
        $re = M()->query($sql);
        $this->addData = arr_to_map($re, 'department_id', 'c');
    }


    /**
    * 设置离职数
    * 思路 user_info.dimission_at >= $this->stDate and user_info.dimission_at < $this->enDate
    */
    private function setDimmision(){
         $sql = "select count(user_id) as c , department_id from rbac_user inner join user_info on rbac_user.id = user_info.user_id where user_info.dimission_at >= '" .$this->stDate."' and user_info.dimission_at< '".$this->enDate."'  group by  user_info.department_id";
         $re = M()->query($sql);
         $this->dimData = arr_to_map($re, 'department_id', 'c');
    }

    private function setDepartments(){
        if (isset($_GET['department_id'])) {
            D('Department')->where(array('id'=>I("get.department_id")));
        }
        $this->departments = D('Department')->where(array('status'=>array('egt',0)))->field('id,name')->select();
    }
}