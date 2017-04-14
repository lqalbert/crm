<?php
namespace Home\Controller;
use Home\Model\CustomerLogModel;
use Home\Model\CustomerModel;
use Home\Model\DepartmentModel;
use Home\Model\RoleModel;
use Common\Lib\User;


class AddCountController extends CommonController{
	protected $pageSize = 15;
	protected $table = "user_info";
	protected $fields = "realname,user_id";
	private function getDayBetween($begin){
		$today = Date("Y-m-d H:i:s",  strtotime($begin)) ;
		/*	echo 'aa';
		print_r($today);
		echo 'bb';*/
		return   array(
					array('egt', $today), 
					array('elt', Date("Y-m-d H:i:s", strtotime("+1 day", strtotime($today))))
		         );
	}

	public function index(){
    $user = new User();
    $searchGroup = $user->getRoleObject()
      ->getCustomerSearchGroup(array(array('value'=>'user','key'=>"个人" ) ,
       array('value'=>'group','key'=>"团组" ),array('value'=>'department','key'=>'部门')));
		$this->assign('searchGroup',$searchGroup);
		$this->display();
	}



	/**
	 * 公用 获取列表
	 *
	 * @return array() || null
	 * 
	 **/
	public function getList(){
		switch (I('get.object')) {
			case 'user':
			     $result = $this->getUserCount(); //基于个人为条件查询
				break;
			case 'group':
			    $result = $this->getGroupCount(); //基于团组为条件查询
				break;
			case 'department':
				  $result = $this->getDepartmentCount(); //基于部门为条件查询
				 break;
			default:
				 $result = $this->getUserCount(); //默认
				break;
		}
		$this->ajaxReturn($result);

	}
	private function getBetween(){
		$date = I('get.dist', Date("Y-m-d")." 00:00:00");

		return $this->getDayBetween($date);
	}

	private function setUserCondition($query){
		$roleM = D('Role');
		if(empty(I('enter_name'))){
			$query->where(array('role_id'=>array('in', array($roleM->getIdByEname(RoleModel::CAPTAIN), $roleM->getIdByEname(RoleModel::STAFF)))));
		}else{
			$query->where(array('role_id'=>array('in', array($roleM->getIdByEname(RoleModel::CAPTAIN), $roleM->getIdByEname(RoleModel::STAFF))),'realname'=>array('like',I('enter_name'))));
		}
		return $query;


	}
	private function setCondition(){
		$object = I("object");
		$enter_name = I("enter_name");
		//设置查询条件
    	$where = array();
    	if(!empty($enter_name)){
			$where["name"] = array (
					"like",
					"%{$enter_name}%" 
			);
		}
		 $where["status"] = 1;
		 if($object=="department"){
		 	$where["type"]=array(
		 		"in",
		 		array(DepartmentModel::CAREER, DepartmentModel::GENERALIZE)
		 		);
		 }
		return $where;

	}
	//客户类型
	private function getCustomerType(){
		return  D("Customer")->getType();
	}
	/**
	* 基于人的录入统计
	*/
	private function getUserCount(){
		$between_today = $this->getBetween();
		$count =  $this->setUserCondition(M('user_info'))->count();
		$list  =  $this->setUserCondition(M('user_info'))->field('user_id ,realname as name')->order('user_id desc')->page(I('get.p',0). ','. $this->pageSize)->select();
		$customerType = $this->getCustomerType();
		foreach ($list as $key => $value) {
			$list[$key]['total_count'] = M('customers_basic')->where(array('user_id'=>$value['user_id'], 'created_at'=>$between_today))->count();
			foreach($customerType as $k=>$v){
				$list[$key][$k.'_count'] = M('customers_basic')->where(array('user_id'=>$value['user_id'],'type'=>$k,  'created_at'=>$between_today))->count();
			}
		}
		return  array('list'=>$list, 'count'=>$count);
	}
	/**
	* 基于小组的录入统计
	*/
	private function getGroupCount(){
		$between_today = $this->getBetween();
		$wheres = $this->setCondition();
		$count = M('group_basic')->where($wheres)->count();
		$list = M('group_basic')->field('id, name')->where($wheres)->order('id desc')->page(I('get.p',0). ','. $this->pageSize)->select();
		foreach ($list as $key => $value) {
			$userList = M('user_info')->where(array('group_id'=>$value['id']))->getField('user_id',true);
			if (empty($userList)) {
				$list[$key]['total_count'] = 0;
			} else {
				$list[$key]['total_count'] = M('customers_basic')->where(array('user_id'=>array('in', $userList), 'created_at'=>$between_today))->count();
			}
			$customerType = $this->getCustomerType();
			foreach($customerType as $k=>$v){
				$list[$key][$k.'_count'] = M('customers_basic')->where(array('user_id'=>array('in', $userList),'type'=>$k,  'created_at'=>$between_today))->count();
			}
		}
		return array('list'=>$list, 'count'=>$count);
	}
	/**
	* 基于部门的录入统计
	*/
	private function getDepartmentCount(){
		$between_today = $this->getBetween();
		$wheres = $this->setCondition();
		$count = M('department_basic')->where($wheres)->count();
		$list  = M('department_basic')->where($wheres)->field('id, name')->page(I('get.p',0). ','. $this->pageSize)->select();
		foreach ($list as $key => $value) {
			$userList = M('user_info')->where(array('department_id'=>$value['id']))->getField('user_id', true);
			if (empty($userList)) {
				$list[$key]['total_count'] = 0;
			} else {
				$list[$key]['total_count'] = M('customers_basic')->where(array('user_id'=>array('in', $userList), 'created_at'=>$between_today))->count();
			}
			$customerType = $this->getCustomerType();
			foreach($customerType as $k=>$v){
				$list[$key][$k.'_count'] = M('customers_basic')->where(array('user_id'=>array('in', $userList),'type'=>$k,  'created_at'=>$between_today))->count();
			}
		}
		return array('list'=>$list, 'count'=>$count);
	}



}


