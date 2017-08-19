<?php
namespace Home\Controller;

use Home\Model\DistributeModel;

class DistributeRecordController extends CommonController{

    protected $table= "DistributeRecord";



    public function index(){
        $this->display();
    }


    public function setQeuryCondition(){
        $roleName = $this->getRoleEname();
        $funcName = $roleName."Condition";
        if (method_exists($this, $funcName)) {
            call_user_func(array($this, $funcName));
        }
    }

    public function goldCondition(){
        $this->M->where(array("type"=>DistributeModel::GOLD));
    }

    public function departmentMasterCondition(){
        $this->M->where(array("type"=>DistributeModel::DEPARTMENT, "obj_id"=>$this->getUserDepartmentId()));
    }

    public function captainCondition(){
        $this->M->where(array("type"=>DistributeModel::GROUP, "obj_id"=>$this->getUserGroupId()));
    }

    protected function _getList(){

        $this->setQeuryCondition();

        $count = (int)$this->M->count();
        $this->setQeuryCondition();

        $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
        D("DistributeRecord")->getDetail($list);
        $result = array('list'=>$list, 'count'=>$count);
        
        return $result;
    }




}