<?php
namespace Home\Controller;
use Home\Model\CustomerLogModel;
use Home\Model\CustomerModel;
use Home\Model\DepartmentModel;
use Home\Model\RoleModel;
use Common\Lib\User;

class QuantizationCountController extends CommonController{
  protected $pageSize = 15;
  protected $table='StatisticsQuantization';
  
  public function index(){
      //部门选项权限
      $ename=$this->getRoleEname();

      //所属部门
      $arr=D('Department')->where(array('id'=>session('account')['userInfo']['department_id']))->field('id,name,type')->select();
      if($ename==RoleModel::GOLD||$ename==RoleModel::HR||$ename==RoleModel::HR_MASTER){
          //总经办、人事经理、人事专员权限
          if($ename==RoleModel::HR&&$arr[0]['type']!='3'){
              $ar=D('Group')->getAllGoups(session('account')['userInfo']['department_id'],'id,name');
              $departments=array('list'=>$arr,'account'=>$arr,'group'=>$ar);
          }else{
              $departments=array('list'=>D('Department')->getSalesDepartments('id,name'),'account'=>'','group'=>'');
          }
          $this->assign('department_id', "");
      }else{
          //部门经理权限
          //部门所属团队小组
          $ar=D('Group')->getAllGoups(session('account')['userInfo']['department_id'],'id,name');
          $departments=array('list'=>$arr,'account'=>$arr,'group'=>$ar);
          
          $this->assign('department_id', session('account')['userInfo']['department_id']);
      }
      $this->assign('departments',$departments);
      $this->display();
  }

  public function setQeuryCondition()
  {
      $this->M->join('user_info ON user_info.user_id=statistics_quantization.user_id')
          ->field('user_info.realname,statistics_quantization.*');
      if(isset($_GET['user_id'])){
          $this->M->where(array('statistics_quantization.user_id'=>I('get.user_id')));
      }
      if (isset($_GET['department_id'])) {
          $this->M->where(array('statistics_quantization.department_id'=>$_GET['department_id']));
      } else if($this->getRoleEname() == RoleModel::DEPARTMENTMASTER){
        $this->M->where(array('statistics_quantization.department_id'=>0));
      }

      if (isset($_GET['group_id'])) {
          $this->M->where(array('statistics_quantization.group_id'=>$_GET['group_id']));
      }
      
      if(isset($_GET['distMin'])&& isset($_GET['distMax'])){
          $this->M->where(
            array('statistics_quantization.date'=>
              array(
                array('EGT', I("get.distMin")), 
                array('ELT', I('get.distMax'))
                )
              )
            );
      }
  }
    /**
     *获取所选部门所包含小组
     */
  public function getAllGroups(){
      if(isset($_GET['department_id'])){
          $arr=D('Group')->getAllGoups($_GET['department_id'],'id,name');
          $this->ajaxReturn($arr);
      }
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
      $users = D('User')->getGroupEmployee($group_id, 'user_id,realname');
    }
    $this->ajaxReturn($users);
  }

}

