<?php
namespace Home\Controller;

use Home\Model\RoleModel;
class IndexDataController extends \Think\Controller{

    private $date = array();

    private function getRoleEname(){
        if (!isset($this->_roleEname)) {
            $this->_roleEname = (new RoleModel)->getEnameById(session('account')['userInfo']['role_id']);
        }
        return $this->_roleEname ;
    }

    private function getCondition(){
        $roleName = $this->getRoleEname();
        $funcName = $roleName."Condition";
        if (method_exists($this, $funcName)) {
           $where = call_user_func(array($this, $funcName));
        } else {
            $where = $this->commonCondtion();
        }
        return $where;
    }

    public function echarts(){
        $this->setDate();
        $this->ajaxReturn($this->setOption());
    }


    public function setOption(){
        if ($this->date) {
            $dateRe = $this->getDateString();
            $where = $this->getCondition();

            $sql = "select sum(today_v) as v , sum(create_num) as c  from statistics_usercustomers where $where and  `date` in (".implode(',', $dateRe).") group by `date` order by `date` desc";
            $re = M()->query($sql);
            $v = array_column($re, 'v');
            $c = array_column($re, 'c');
            $dd=array(
                'date'=>$this->date ,
                'series'=>array(
                    array('name'=>'自锁数','type'=>'bar','data'=>$c),
                    // array('name'=>'成交数','type'=>'bar','data'=>$v),
                 )
            );
        } else {
            $dd=array(
                'date'=>$this->date ,
                'series'=>array(
                    array('name'=>'自锁数','type'=>'bar','data'=>array(0)),
                    // array('name'=>'成交数','type'=>'bar','data'=>array(0)),
                 )
            );
        }
        
        return $dd;
    }

    private function departmentMasterCondition(){
        return  "department_id=". session('account')['userInfo']['department_id'];
    }

    private function captainCondition(){
        return  "group_id=". session('account')['userInfo']['group_id'];
    }

    private function staffCondition(){
        return  "user_id=". session('account')['userInfo']['user_id'];
    }

    private function commonCondtion(){
        return "1";
    }



    private function setDate(){
        $re = M('statistics_usercustomers')->limit(7)->order(' `date` desc')->field(" distinct `date` ")->select();
        $this->date = array_column($re, 'date');
    }

    private function getDateString(){
        $re = array();
        foreach ($this->date as $value) {
            $re[] = "'".$value."'";
        }
        return $re;
    }

    public function getSysReport(){
        $this->setDate();
        $dateRe = $this->getDateString();
        $where = $this->getCondition();
        $sql = "select sum(today_v) as v ,`date` from statistics_usercustomers where $where and  `date` in (".implode(',', $dateRe).") group by `date` order by `date` desc";
        $re = M()->query($sql);
        
        $this->ajaxReturn($re);
    }

    public function getData3(){
        $this->setDate();
        $dateRe = $this->getDateString();
        $where = $this->getCondition();
        $sql = "select sum(phone_track) as phone_c, sum(self_track) as self_c, sum(qq_track) as  qq_c ,`date` from statistics_quantization where  $where  and `date` in (".implode(',', $dateRe).") group by `date` order by `date` desc";
        $re = M()->query($sql);
        
        $this->ajaxReturn($re);
    }

    public function getData2(){
        $this->setDate();
        $dateRe = $this->getDateString();
        $where = $this->getCondition();
        $sql = "select sum(all_num) as all_num , sum(create_num) as create_c ,sum(today_v) as today_v ,`date` from statistics_usercustomers where $where and  `date` in (".implode(',', $dateRe).") group by `date` order by `date` desc";
        $re = M()->query($sql);
        
        $this->ajaxReturn($re);
    }


}