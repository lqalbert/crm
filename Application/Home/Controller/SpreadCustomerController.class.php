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

        $this->display();
    }

    public function setQeuryCondition(){
        $roleName = $this->getRoleEname();
        $functionName = $roleName."Condition";

        if (method_exists($this, $functionName)) {
            call_user_func(array($this, $functionName));
        } 

        $this->M->join("left join department_basic as db1 on customers_basic.spread_id=db1.id")
                ->join("left join department_basic as db2 on customers_basic.depart_id=db2.id")
                ->join("left join user_info as ui1 on customers_basic.user_id=ui1.user_id")
                ->join("left join user_info as ui2 on customers_basic.salesman_id=ui2.user_id")
                ->field("customers_basic.name,customers_basic.id, CONCAT(db1.name,' - ',ui1.realname) as spread_name,CONCAT(db2.name,' - ',ui2.realname) as sale_name");
    }




    private function goldCondition(){
        $this->M->where(array('spread_id'=>array("NEQ", 0)));
        $type = I("get.type",0);
        $this->setDisCondition($type, 'depart_id');
    }

    private function departmentMasterCondition(){
        $this->M->where(array('depart_id'=>$this->getUserDepartmentId()));
        $type = I("get.type",0);
        $this->setDisCondition($type, 'to_gid');
    }

    private function captainCondition(){
        $this->M->where(array('to_gid'=>$this->getUserGroupId()));
        $type = I("get.type",0);
        $this->setDisCondition($type, 'salesman_id');
        // $this->M->field("");
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