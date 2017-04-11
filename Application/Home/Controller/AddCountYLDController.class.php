<?php
namespace Home\Controller;
use Home\Model\CustomerLogModel;
use Home\Model\DepartmentModel;
use Home\Model\RoleModel;
use Common\Lib\User;


class AddCountYLDController extends CommonController{
	protected $pageSize = 15;
	protected $table = "user_info";
	protected $fields = "realname,user_id";
	private function getDayBetween($begin){
		$today = $begin ;
		return   array(
					array('GT', $today), 
					array('LT', Date("Y-m-d H:i:s", strtotime("+1 day", strtotime($today))))
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
			    // $result = $this->getUserCount(); //以前的
			     $result = $this->getGroupUserCount();
				break;
			case 'group':
			     //$result = $this->getGroupCount(); //以前的
			     $result = $this->getDepGroupCount();
				break;
			case 'department':
				 // $result = $this->getDepartmentCount(); //以前的
			      $result = $this->getDepCount();
				 break;
			default:
				// $result = $this->getUserCount(); //以前的
				 $result = $this->getGroupUserCount();
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
		$query->where(array('role_id'=>array('in', array($roleM->getIdByEname(RoleModel::CAPTAIN), $roleM->getIdByEname(RoleModel::STAFF)))));
		return $query;
	}


	/**
	* 基于人的录入统计
	*/
	private function getUserCount(){
		$between_today = $this->getBetween();
		$count =  $this->setUserCondition(M('user_info'))->count();
		$list  =  $this->setUserCondition(M('user_info'))->field('user_id ,realname as name')->page(I('get.p',0). ','. $this->pageSize)->select();
		foreach ($list as $key => $value) {
			$list[$key]['count'] = M('customers_basic')->where(array('user_id'=>$value['user_id'], 'created_at'=>$between_today))->count();
		}

		return  array('list'=>$list, 'count'=>$count);
	}

	/**
	* 基于小组的录入统计
	*/
	private function getGroupCount(){

		$between_today = $this->getBetween();
		$count = M('group_basic')->where(array('status'=>1))->count();
		$list = M('group_basic')->field('id, name')->where(array('status'=>1))->page(I('get.p',0). ','. $this->pageSize)->select();
		foreach ($list as $key => $value) {
			$userList = M('user_info')->where(array('group_id'=>$value['id']))->getField('user_id',true);

			if (empty($userList)) {
				$list[$key]['count'] = 0;
			} else {
				$list[$key]['count'] = M('customers_basic')->where(array('user_id'=>array('in', $userList), 'created_at'=>$between_today))->count();
			}
		}
		return array('list'=>$list, 'count'=>$count);
	}


	/**
	* 基于部门的录入统计
	*/
	private function getDepartmentCount(){
		$between_today = $this->getBetween();
		$count = M('department_basic')->where(array('type'=>array('in', array(DepartmentModel::CAREER, DepartmentModel::GENERALIZE)),'status'=>1))
		                              ->count();
		$list  = M('department_basic')->where(array('type'=>array('in', array(DepartmentModel::CAREER, DepartmentModel::GENERALIZE)),'status'=>1))
		                              ->field('id, name')
		                              ->page(I('get.p',0). ','. $this->pageSize)
		                              ->select();
		foreach ($list as $key => $value) {
			$userList = M('user_info')->where(array('department_id'=>$value['id']))->getField('user_id', true);
			if (empty($userList)) {
				$list[$key]['count'] = 0;
			} else {
				$list[$key]['count'] = M('customers_basic')->where(array('user_id'=>array('in', $userList), 'created_at'=>$between_today))->count();
			}
			
		}
		return array('list'=>$list, 'count'=>$count);
	}

//<=====================================上面是以前的======================下面是现在的======================================================

/**
* 基于小组下面成员的客户录入统计
*/
private function getGroupUserCount(){
	$between_today = $this->getBetween();
	$group_id=session('account')['userInfo']['group_id'];
	$department_id=session('account')['userInfo']['department_id'];
	switch (session('account')['userInfo']['role_id']) {
		case '6':
		  //小组主管看到其组成员的录入统计
			$count =  M('user_info')->where(array('group_id'=>$group_id))->count();
			$list  =  M('user_info')->where(array('group_id'=>$group_id))->field('user_id ,realname as name')->page(I('get.p',0). ','. $this->pageSize)->select();
			foreach ($list as $key => $value) {
				$list[$key]['count'] = M('customers_basic')->where(array('user_id'=>$value['user_id'], 'created_at'=>$between_today))->count();
			}
			return  array('list'=>$list, 'count'=>$count);
			break;
		case '5':
			//部门下面所有小组下的所有成员的录入统计
		  $groupList=M('group_basic')->where(array('department_id'=>$department_id,'status'=>1))->getField('id',true);
			$count =  M('user_info')->where(array('group_id'=>array('IN',$groupList),'department_id'=>$department_id))->count();
			$list  =  M('user_info')->where(array('group_id'=>array('in',$groupList),'department_id'=>$department_id))->field('user_id ,realname as name')->page(I('get.p',0). ','. $this->pageSize)->select();
			//var_dump($groupList);
			foreach ($list as $key => $value) {
				$list[$key]['count'] = M('customers_basic')->where(array('user_id'=>$value['user_id'], 'created_at'=>$between_today))->count();
			}
			return  array('list'=>$list, 'count'=>$count);
			break;
		case '1':
			//所有部门下的所有成员的录入统计
			$count =  M('user_info')->where(array('department_id'=>array('GT','0')))->count();
			$list  =  M('user_info')->where(array('department_id'=>array('GT','0')))->field('user_id ,realname as name')->page(I('get.p',0). ','. $this->pageSize)->select();
			foreach ($list as $key => $value) {
				$list[$key]['count'] = M('customers_basic')->where(array('user_id'=>$value['user_id'], 'created_at'=>$between_today))->count();
			}
			return  array('list'=>$list, 'count'=>$count);
			break;
		default:
			# code...
			break;
	}

}

/**
* 基于部门下的其所有小组的录入统计
*/
private function getDepGroupCount(){
	$between_today = $this->getBetween();
	$department_id=session('account')['userInfo']['department_id'];
  switch (session('account')['userInfo']['role_id']) {
  	case '5':
  	  //部门下面所有小组的录入统计
      $count = M('group_basic')->where(array('department_id'=>$department_id,'status'=>1))->count();
			$list = M('group_basic')->field('id, name')->where(array('department_id'=>$department_id,'status'=>1))->page(I('get.p',0). ','. $this->pageSize)->select();
			foreach ($list as $key => $value) {
				$userList = M('user_info')->where(array('group_id'=>$value['id']))->getField('user_id',true);

				if (empty($userList)) {
					$list[$key]['count'] = 0;
				} else {
					$list[$key]['count'] = M('customers_basic')->where(array('user_id'=>array('in', $userList), 'created_at'=>$between_today))->count();
				}
			}
			return array('list'=>$list, 'count'=>$count);
  		break;
  	case '1':
  	  //所有部门下的小组的录入统计
      $count = M('group_basic')->where(array('status'=>1))->count();
			$list = M('group_basic')->field('id, name')->where(array('status'=>1))->page(I('get.p',0). ','. $this->pageSize)->select();
			foreach ($list as $key => $value) {
				$userList = M('user_info')->where(array('group_id'=>$value['id']))->getField('user_id',true);

				if (empty($userList)) {
					$list[$key]['count'] = 0;
				} else {
					$list[$key]['count'] = M('customers_basic')->where(array('user_id'=>array('in', $userList), 'created_at'=>$between_today))->count();
				}
			}
			return array('list'=>$list, 'count'=>$count);
  		break;
  	default:
  		# code...
  		break;
  }

}

/**
* 基于所有部门的录入统计
*/
private function getDepCount(){
	$between_today = $this->getBetween();
	$count = M('department_basic')->where(array('status'=>1))->count();
	$list  = M('department_basic')->where(array('status'=>1))->field('id, name')->page(I('get.p',0). ','. $this->pageSize)->select();
	foreach ($list as $key => $value) {
		$userList = M('user_info')->where(array('department_id'=>$value['id']))->getField('user_id', true);
		if (empty($userList)) {
			$list[$key]['count'] = 0;
		} else {
			$list[$key]['count'] = M('customers_basic')->where(array('user_id'=>array('in', $userList), 'created_at'=>$between_today))->count();
		}
		
	}
	return array('list'=>$list, 'count'=>$count);
}






















}


