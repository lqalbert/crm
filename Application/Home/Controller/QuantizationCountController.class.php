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
              $departments=array('list'=>D('Department')->getAllDepartments('id,name'),'account'=>'','group'=>'');
          }
      }else{
          //部门经理权限
          //部门所属团队小组
          $ar=D('Group')->getAllGoups(session('account')['userInfo']['department_id'],'id,name');
          $departments=array('list'=>$arr,'account'=>$arr,'group'=>$ar);
      }
      $this->assign('departments',$departments);
      $this->display();
  }

  public function setQeuryCondition()
  {
      $this->M->join('user_info ON user_info.user_id=statistics_quantization.user_id')
          ->field('user_info.*,statistics_quantization.*');
      if(I('get.realname')){
          $this->M->where(array('realname'=>array('like',I('get.realname')."%")));
      }
      if (isset($_GET['department_id'])) {
          $this->M->where(array('statistics_quantization.department_id'=>$_GET['department_id']));
      }
      if (isset($_GET['group_id'])) {
          $this->M->where(array('statistics_quantization.group_id'=>$_GET['group_id']));
      }
      if(isset($_GET['dist'])){
          $this->M->where(array('statistics_quantization.date'=>$_GET['dist']));
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

}

