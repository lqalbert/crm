<?php
namespace Home\Controller;

class SpreadCustomerForSaleController extends CommonController{

    protected $table = "Customer";
    
    public function index(){
        // $this->assign("departments", D("Department")->getGoodSalesDepartments());
        $this->assign("groups",     D("Group")->getAllGoups($this->getUserDepartmentId() ,"id,name"));
        $this->display();
    }

    public function setQeuryCondition(){
        $roleName = $this->getRoleEname();
        $functionName = $roleName."Condition";

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
        $this->M->where(array('spread_id'=>array("NEQ", 0)));
    }

    private function setDepart(){
        $depart_id  = $this->getUserDepartmentId();
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

        if (!empty(I("get.checked"))) {
            $this->M->where(array('customers_basic.to_gid'=>0 ));
        }
        
    }

    public function getList(){

        $result = $this->_getList();
        
        foreach ($result['list'] as &$value) {
            $value['contacts'] = M("customers_contacts")->where(array('cus_id'=>$value['id']))->select();
        }

        if (IS_AJAX) {
            $this->ajaxReturn($result);
        }  else {
            return $result;
        }

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