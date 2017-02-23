<?php
namespace Home\Controller;
use Home\Model\CustomerLogModel;
use Home\Model\DepartmentModel;
use Home\Model\RoleModel;



class AddCountController extends CommonController{
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
			     $result = $this->getUserCount();
				break;
			case 'group':
			     $result = $this->getGroupCount();
				break;
			case 'department':
				 $result = $this->getDepartmentCount();
				 break;
			default:
				 $result = $this->getUserCount();
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




























}


