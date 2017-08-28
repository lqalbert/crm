<?php
namespace Home\Controller;
/**
* 给总经办 部门经理 小组主管 
* 看的界面
* 客户  推广部-员工   销售部（小组）-员工(未分配员工为空) 
*/

class SpreadCustomerController extends CommonController {

    protected $table = "Customer";
    
    public function index(){
        $this->assign("departments", D("Department")->getGoodSalesDepartments());
        $this->assign("spreads", D("Department")->getSpreadDepartments());
        $this->display();
    }

    public function setQeuryCondition(){
        $roleName = $this->getRoleEname();
        $functionName = $roleName."Condition";

        // if (method_exists($this, $functionName)) {
        //     call_user_func(array($this, $functionName));
        // } 
        
        // $type = I("get.type",0);
        // $this->setDisCondition($type, 'depart_id');
        if (I('get.name')) {
            $this->M->where(array('customers_basic.name'=>array("like", I('get.name')."%")));
        }

        $this->setSpread();
        $this->setDepart();


        $this->M->join("left join department_basic as db1 on customers_basic.spread_id=db1.id")
                ->join("left join department_basic as db2 on customers_basic.depart_id=db2.id")
                ->join("left join user_info as ui1 on customers_basic.user_id=ui1.user_id")
                ->join("left join user_info as ui2 on customers_basic.salesman_id=ui2.user_id")
                ->join("left join group_basic as gb on customers_basic.to_gid=gb.id")
                ->field("customers_basic.name,customers_basic.id,customers_basic.created_at,customers_basic.dis_time,customers_basic.type, CONCAT(db1.name,' - ',ui1.realname) as spread_name,db2.name as depart_name, gb.name as g_name ,ui2.realname as sale_name");
    }



    private function setSpread(){
        $spread_id  = I("get.spread_id",0);
        $spread_gid = I("get.spread_gid",0);
        $spread_uid = I("get.spread_uid",0);

        if (!empty($spread_uid)) {
            $this->M->where(array('customers_basic.user_id'=>$spread_uid));
            return ;
        }

        if (!empty($spread_gid)) {
            $user_ids = M("user_info")->where(array('group_id'=>$spread_gid))->getField("user_id",true);
            if ($user_ids) {
                $this->M->where(array('customers_basic.user_id'=>array('IN', $user_ids)));
            } else {
                $this->M->where(array('customers_basic.user_id'=>0));
            }
            return ;
        }


        if (empty($spread_id)) {
            $this->M->where(array('spread_id'=>array("NEQ", 0)));
        } else {
            $this->M->where(array('spread_id'=>$spread_id ));
        }
    }

    private function setDepart(){
        $depart_id  = I("get.depart_id",0);
        $depart_gid = I("get.depart_gid",0);
        $depart_uid = I("get.depart_uid",0);

        if (!empty($depart_uid)) {
            $this->M->where(array('customers_basic.salesman_id'=>$depart_uid));
            return ;
        }

        if (!empty($depart_gid)) {
            $this->M->where(array('customers_basic.to_gid'=>$depart_gid));
            return ;
        }


        if (!empty($depart_id)) {
            $this->M->where(array('customers_basic.depart_id'=>$depart_id ));
        }
    }

    public function getList(){

        $result = $this->_getList();
        
        foreach ($result['list'] as &$value) {
            $value['contacts'] = M("customers_contacts")->where(array('cus_id'=>$value['id']))->select();
        }




        if (IS_AJAX) {
            $this->ajaxReturn($result);
            // $this->ajaxReturn($this->M->getLastSql());
        }  else {
            
            return $result;
        }

    }



    public function getGroups(){
        $this->ajaxReturn(D("Group")->getAllGoups(I('get.id'),"id,name"));
    }

    public function getUsers(){
        $this->ajaxReturn(D("User")->getGroupEmployee(I('get.id'),"id,realname as name"));
    }




    private function setDisCondition($type, $field){
        switch ($type) {
            case 1: //已分配
                $this->M->where(array($field=>array("NEQ",0)));
                break;
            case 2: //未分配
                $this->M->where(array($field=>0));
                break;
            
            default:
                # code...
                break;
        }
    }






}