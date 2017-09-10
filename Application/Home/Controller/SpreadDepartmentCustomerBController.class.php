<?php
namespace Home\Controller;

use Home\Model\RoleModel;
class SpreadDepartmentCustomerBController extends DistributeCustomerBController{

    

    public function index(){
    
        $this->setOptions();
        $this->assign("uid", session("uid"));
        $this->assign("gid", $this->getUserGroupId());

        $this->display();
    }

    protected function getSearchGroup(){
        $depart_id = $this->getUserDepartmentId();

        if ($depart_id!=0) {
            return D("Group")->getAllGoups($depart_id, 'id,name');
        } else {
            return  array();
        }
        
    }

    
    /**
    * 设置查询参数
    * 
    * @return null
    */
    public function setQeuryCondition() {
        if (I('get.name')) {
            $this->M->where(array("customers_basic.name"=> array('like', I('get.name')."%")));
        }

        if (I('get.contact')) {
            $cus_ids = M("customers_contacts")->where(array("phone|qq|weixin"=>array("like", I('get.contact')."%")))->getField("cus_id");
            if ($cus_ids) {
                $this->M->where(array("customers_basic.id"=>array("IN", $cus_ids )) );
            } else {
                $this->M->where(array("customers_basic.id"=>0));
            }
        } else if(I("get.phone")){
            $cus_ids = M("customers_contacts")->where(array("phone"=>array("like", I('get.phone')."%")))->getField("cus_id");
            if ($cus_ids) {
                $this->M->where(array("customers_basic.id"=>array("IN",$cus_ids )));
            } else {
                $this->M->where(array("customers_basic.id"=>0));
            }
        }

       

       

        if (I("get.start") && I("get.end")) {
            $start = str_replace("/", "-", I("get.start"));
            $end   = str_replace("/", "-", I("get.end"))." 23:59:59";

            $this->M->where(array("customers_basic.created_at"=>array(array('EGT', $start), array('ELT', $end))));
        }


        if (I("get.dis")) {
            $this->M->where(array("customers_basic.depart_id"=>array('NEQ', 0)));
        }

        if(I("get.uid")){
            $this->M->where(array("customers_basic.user_id"=>I("get.uid")));
        } else if(I("get.gid")) {
            $userIds = D("User")->getGroupEmployee(I("get.gid"), 'id');
            if ($userIds) {
                $this->M->where(array("customers_basic.user_id"=>array("IN", array_column($userIds, "id"))));
            } else {
                $this->M->where(array("customers_basic.user_id"=>0));
            }
        } else {
            $userIds = D("User")->getDepartmentEmployee($this->getUserDepartmentId(), 'id');
            if ($userIds) {
                $this->M->where(array("customers_basic.user_id"=>array("IN", array_column($userIds, "id"))));
            } else {
                $this->M->where(array("customers_basic.user_id"=>0));
            } 
        }
    

    }

}