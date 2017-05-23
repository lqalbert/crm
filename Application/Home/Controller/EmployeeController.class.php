<?php
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\DepartmentModel;

class EmployeeController extends CommonController {
	protected $table="RbacUser";
	protected $pageSize = 13;


	public function index (){


		$this->assign("roleList", $this->getRoles());
		$this->assign("groupList", D('Group')->where(array('status'=>1))->select());
		$this->assign("sexType", array("未定义", "男", "女"));
		$ename = $this->getRoleEname();
    	$this->assign('viewDecorator', $this->M->decoratorView($ename));
		$this->display();
	}

	public function setQeuryCondition() {

		// $this->M->relation(true)->field('password',true)->where(array('no_authorized'=>0));
		$this->M->join('user_info ON rbac_user.id = user_info.user_id')
		        ->field('account,address,
		        	area_city,area_district,
		        	area_province,created_at,
		        	department_id,group_id,
		        	head,id,mphone,no_authorized,phone,
		        	qq,qq_nickname,realname,role_ename,role_id,sex,status,user_id,weixin,weixin_nikname,id_card,card_img,card_front,card_back,ip,location,lg_time,out_time')->where(array('no_authorized'=>0))
		        ->where(array('rbac_user.status'=>array('EGT',0)));

		/*$user = new User;
		$user->getRoleObject();
		$user->setEmployQueryCondition($this->M);*/
		$this->setRoleCondition();

		if (isset($_GET['name'])) {
			$this->M->where(array('account'=>array('like', I('get.name')."%")));
		}

		$this->M->where(array('rbac_user.id'=>array('neq', session('uid'))));


	}

	private function isHrDeparment(){
		if (!isset($this->departmentRow)) {
			$this->departmentRow = D('Department')->find($this->getDepartmentId());
		}
		
		if ($this->departmentRow['type'] == DepartmentModel::HR_DEPARTMENT) {
			return true; //$_POST['department_id'] = 0;
		} else {
			return false; //$_POST['department_id'] = session('account')['userInfo']['department_id'];
		}
	}

	private function getDepartmentId(){
		// var_dump(session('account')['userInfo']['department_id']);
		if ($this->getRoleEname()=='gold') {
			return 0;
		} else {
			$depart_id = session('account')['userInfo']['department_id'];
			
			if ($depart_id == 0) {
				return 9999999;
			} else {
				return $depart_id;
			}
		}
		
	}

	

	private function setDeparmentQuery(){
		$departmentRow = $this->departmentRow;
		$config = json_decode($departmentRow['config'], true);
		if (isset($config['EmployeeQueryCondition'])) {
			call_user_func(array($this, 'set'.$config['EmployeeQueryCondition']));
		}
	}



	private function setDepartmentEmployee(){
		$this->M->where(array(
			'user_info.department_id'=>array('eq', $this->getDepartmentId()),
			// 'role_id'=>array('NEQ', array()) 
		));

	}
	private function setAllEmployee(){
		
	}
	// 
	private function goldCondition(){
		$this->setAllEmployee();
	}

	//人事
	private function humanResourceCondition(){

		if ($this->isHrDeparment()) {
			$this->setAllEmployee();
		} else {
			$this->setDeparmentQuery();
		}
		
	}

	//人事经理
	private function hrMasterCondition(){
		$this->setAllEmployee();
	}

	//风控经理
	private function riskMasterCondition(){
		$this->setDepartmentEmployee();
	}
	//客服经理
	private function serviceMasterCondition(){
		$this->setDepartmentEmployee();
	}



	//部门经理
	private function departmentMasterCondition(){
		$this->setDepartmentEmployee();
	}


	public function setRoleCondition(){
		$this->roleEname = $this->getRoleEname();
        $funcName = $this->roleEname."Condition";
        if (method_exists($this, $funcName)) {
             call_user_func(array($this, $funcName));
        } else {
        	$this->error("没有权限EmployeeController");
        }
	}

	



	public function getRoles(){
		/*$row = M('rbac_role')->field('level')->find(session('account')['userInfo']['role_id']);
		return D('rbac_role')->where(array('level'=>array('gt', $row['level'])))->select();*/
		$departRow = D("Department")->find(session('account')['userInfo']['department_id']);
		if ($departRow) {
			return D("Department")->getEmployeeRoles($departRow['type']);
		} else {
			return D('rbac_role')->select();
		}
		
	}


	/**
	* 获取用户 角色 id
	*
	*/
	public function getUserRoles(){
		$result = D('rbac_role_user')->where(array('user_id'=> I('get.user_id',0)))->select();
		$this->ajaxReturn($result);
	}

	/**
	* 设置用户 角色 id
	*/
	public function setUserRoles(){
		$M = D('rbac_role_user');
		$user_id = I('post.user_id',0);
		$role_ids = I('post.role_ids');
		$insert_list = array();
		if (is_array($role_ids)) {
			foreach ($role_ids as $value) {
				$insert_list[] = array('role_id'=>$value, 'user_id'=>$user_id);
			}
		} else {
			$insert_list[] = array('role_id'=>$role_ids, 'user_id'=>$user_id);
		}
		
		$M->startTrans(); 
		$result = $M->where(array('user_id'=>$user_id))->delete();

		if ($result !== false) {
			$insert_result = $M->addAll($insert_list);
			if ($insert_result !== false) {
				$re = M('user_info')->data(array('user_id'=>$user_id, 'role_id'=>$role_ids))->save();
				if ($re !== false) {
					$M->commit();
					$this->success("操作成功");
				} else {
					$M->rollback();
					$this->error("操作失败".M('user_info')->getError());
				}
				
			} else {
				$M->rollback();
				$this->error("操作失败".$M->getError());
			}
		} else {
			$M->rollback();
			
			$this->error("操作失败".$M->getError());
		}
	}

	/**
	* 预处理
	*/
	public function _before_add(){
		$this->rightProcted();
		/*$user = new User;
		$user->getRoleObject();
		$user->setEmployeeAddData();*/

		//如果是人事部 则添加的员工部门为 0
		//如果不是人事部 则添加的员工部门为 当前员工的部门


		// $departmentRow = D('Department')->find($this->getDepartmentId());
		if ($this->isHrDeparment()) {
			$_POST['department_id'] = 0;
		} else {
			$_POST['department_id'] = session('account')['userInfo']['department_id'];
		}

		
	}

	/**
	* 添加
	*/
	public function add(){
        
		$re = $this->M->create($_POST, 1);
		if ($re) {
			$this->M->startTrans(); 
			$re['userInfo'] = M('userInfo')->create($_POST, 1);
			if (empty($re['userInfo']['head'])) {
				unset($re['userInfo']['head']);
			}
			$id = $this->M->relation('userInfo')->add($re);
			if ($id) {
				$role_list = array('role_id'=>$re['userInfo']['role_id'], 'user_id'=>$id);
				if (M('rbac_role_user')->add($role_list)) {
					$this->M->commit();
					$this->success(L('ADD_SUCCESS'));
				} else {
					$this->M->rollback();
					$this->error(M('rbac_role_user')->getError());
				}
			} else {
				$this->M->rollback();
				$this->error($this->M->getError());
			}
		} else {
			$this->error($this->M->getError());
		}

	}

	/**
	* 编辑
	*/
	public function edit(){
		//新方法
        $re = M('userInfo')->create($_POST, 2);
		if ($re) {
			if (M('userInfo')->where(array('user_id'=>I('post.id') ))->save() !== false) {
				$this->success(L('ADD_SUCCESS'));
			} else {
				$this->error($this->M->getError().$this->M->getLastSql());
			}
		} else {
			$this->error($this->M->getError().$this->M->getLastSql());
		}

	}


	public function changePassword(){
		$re = $this->M->create($_POST, 2);
		if ($re) {
			if ($this->M->save() !== false) {
				$this->success(L('ADD_SUCCESS'));
			} else {
				$this->error($this->M->getError().$this->M->getLastSql());
			}
		} else {
			$this->error($this->M->getError().$this->M->getLastSql());
		}
	}

	/*public function _before_delete() {
		$this->setQeuryCondition();
	}*/
}
