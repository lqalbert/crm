<?php
namespace Home\Controller;
use Home\Model\CustomerLogModel;
use Home\Model\CustomerModel;
use Home\Model\DepartmentModel;
use Home\Model\RoleModel;
use Common\Lib\User;

class AchievementRankController extends CommonController{
  protected $pageSize = 15;
  
  public function index(){
  	$this->display();
  }

  public function getList(){
  	$type = I('get.type');
  	$start = I('get.start');
  	$end = I('get.end');
  	switch ($type) {
  		case 'order_num':
  			$re = $this->getOrderRank($start,$end);
  			break;
  		case 'create_num':
  			$re = $this->getCreateRank($start,$end);
  			break;
  		default:
  			$re = $this->getOrderRank($start,$end);
  			break;
  	}

    $this->ajaxReturn($re);
  }

  protected function getOrderRank($start,$end){
  	$depRank = $this->getDepOrderRank($start,$end);

    $groupRank = $this->getGroupOrderRank($start,$end);

    $userRank = $this->getUserOrderRank($start,$end);
    $reArr = array(
      'dep'=>$depRank,
      'group'=>$groupRank,
      'user'=>$userRank
    ); 
  	return $reArr;
  }
  
  protected function getCreateRank($start,$end){
  	$depRank = $this->getDepCreateRank($start,$end);

    $groupRank = $this->getGroupCreateRank($start,$end);

    $userRank = $this->getUserCreateRank($start,$end);

    $reArr = array(
      'dep'=>$depRank,
      'group'=>$groupRank,
      'user'=>$userRank
    ); 
  	return $reArr;

  }

  protected function getDepOrderRank($start,$end){
  	$depRank = M('statistics_sale_achievement')->field('sum(order_num) as order_num,department_name as name,department_id')
  	     ->where(array('date'=>array(array('EGT',$start),array('ELT',$end))))->group('department_id')
  	     ->order('order_num desc')->limit("0,15")->select();
  	return $depRank;
  }

  protected function getGroupOrderRank($start,$end){
    $groupRank = M('statistics_sale_achievement')->field("sum(order_num) as order_num,concat(department_name,'-',group_name) as name,group_id,department_name,department_id")
  	     ->where(array('date'=>array(array('EGT',$start),array('ELT',$end))))->group('group_id')
  	     ->order('order_num desc')->limit("0,15")->select();
  	return $groupRank;
  }

  protected function getUserOrderRank($start,$end){
    $userRank = M('statistics_sale_achievement as ssa')->field("sum(order_num) as order_num,concat(concat(department_name,'-',group_name),'-',ui.realname) as name,ssa.user_id,ssa.group_id,group_name,department_name,ssa.`department_id`")
  	     ->join("left join user_info as ui on ui.user_id=ssa.user_id")
  	     ->where(array('ssa.date'=>array(array('EGT',$start),array('ELT',$end))))->group('ssa.user_id')
  	     ->order('order_num desc')->limit("0,15")->select();
  	return $userRank;
  }

  protected function getDepCreateRank($start,$end){
  	$depRank = M('statistics_usercustomers')->field('sum(create_num) as create_num,department_name as name,department_id')
  	     ->where(array('date'=>array(array('EGT',$start),array('ELT',$end))))->group('department_id')
  	     ->order('create_num desc')->limit("0,15")->select();
  	return $depRank;
  }

  protected function getGroupCreateRank($start,$end){
    $groupRank = M('statistics_usercustomers')->field("sum(create_num) as create_num,concat(department_name,'-',group_name) as name,group_id,department_name,department_id")
  	     ->where(array('date'=>array(array('EGT',$start),array('ELT',$end))))->group('group_id')
  	     ->order('create_num desc')->limit("0,15")->select();
  	return $groupRank;
  }

  protected function getUserCreateRank($start,$end){
    $userRank = M('statistics_usercustomers as su')->field("sum(create_num) as create_num,concat(concat(department_name,'-',group_name),'-',ui.realname) as name,su.user_id,su.group_id,group_name,department_name,su.`department_id`")
  	     ->join("left join user_info as ui on ui.user_id=su.user_id")
  	     ->where(array('su.date'=>array(array('EGT',$start),array('ELT',$end))))->group('su.user_id')
  	     ->order('create_num desc')->limit("0,15")->select();
  	return $userRank;
  }





}