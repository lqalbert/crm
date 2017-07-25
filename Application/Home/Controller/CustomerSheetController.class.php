<?php
namespace Home\Controller;
use Home\Model\CustomerLogModel;
use Home\Model\CustomerModel;
use Home\Model\DepartmentModel;
use Home\Model\RoleModel;
use Common\Lib\User;

class CustomerSheetController extends CommonController{
  protected $pageSize = 15;
  protected $table = "statistics_step";
  
  public function index(){
    $this->assign("departments", $this->getDepartments());
    $this->display();
  }

  private function getDepartments(){
    $ename=$this->getRoleEname();
    $funcName = $ename."Departments";
    if (method_exists($this, $funcName)) {
        return call_user_func(array($this, $funcName));
    } else {
        return array();
    }
  }

  private function goldDepartments(){
    $this->assign("depart_id", "");
    return D("Department")->getSalesDepartments();
  }

  private function departmentMasterDepartments(){
    $depart_id = $this->getDepartment_id();
    $this->assign("depart_id", $depart_id);
    return D("Department")->getTheDepartments( $depart_id );
  }

  private function getDepartment_id(){
    return session('account')['userInfo']['department_id'];
  }

  public function getAllGroups(){
    $department_id = I("get.department_id");
    $groups = D("group")->getAllGoups($department_id, 'id,name');
    $this->ajaxReturn($groups);
  }

  public function getAllUser(){
    $group_id = I("get.group_id");
    $distMin  = I("get.distMin", null);
    $distMax  = I("get.distMax", null);

    $this->M->where(array("group_id"=>$group_id));
    if ($distMin && $distMax) {
      $this->M->where(array('date'=>array(array('EGT', $distMin), array("ELT", $distMax))));
    }
    $user_ids =  array_keys(array_flip($this->M->getField('user_id', true))) ;

    if ($user_ids) {
      $users = M('user_info')->field("user_id, realname")->where(array("user_id"=>array("IN", $user_ids)))->select();
    } else {
      $users = array();
    }
    $this->ajaxReturn($users);
  }


  public function setQeuryCondition()
  {
      if(isset($_GET['user_id'])){
          $this->M->where(array('statistics_step.user_id'=>I('get.user_id')));
      }
      if (isset($_GET['department_id'])) {
          $this->M->where(array('statistics_step.department_id'=>I('get.department_id')));
      } else if($this->getRoleEname() == RoleModel::DEPARTMENTMASTER){
        $this->M->where(array('statistics_step.department_id'=>0));
      }
      if (isset($_GET['group_id'])) {
          $this->M->where(array('statistics_step.group_id'=>I('get.group_id')));
      }
      if(isset($_GET['distMin'])&& isset($_GET['distMax'])){
          $this->M->where(array('date'=>array(array('EGT', I('get.distMin')), array("ELT", I('get.distMax')))));
      }

      $this->M->join('user_info on statistics_step.user_id = user_info.user_id')
              ->field("statistics_step.*,user_info.realname");
  }
	
}