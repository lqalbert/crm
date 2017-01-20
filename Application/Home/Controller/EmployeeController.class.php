<?php
namespace Home\Controller;

class EmployeeController extends CommonController {
	protected $table="RbacUser";


	public function index (){
		$this->assign("roleList", $this->getRoles());
		$this->assign("groupList", D('Group')->where(array('status'=>1))->select());
		$this->assign("sexType", array("未定义", "男", "女"));
		$this->display();
	}

	public function setQeuryCondition() {

		$this->M->relation(true)->field('password',true);
		if (isset($_GET['name'])) {
			$this->M->where(array('account'=>array('like', I('get.name')."%")));
		}
	}

	public function getRoles(){
		return D('rbac_role')->select();
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
		foreach ($role_ids as $value) {
			$insert_list[] = array('role_id'=>$value, 'user_id'=>$user_id);
		}
		$M->startTrans(); 
		$result = $M->where(array('user_id'=>$user_id))->delete();

		if ($result !== false) {
			$insert_result = $M->addAll($insert_list);
			if ($insert_result !== false) {
				$M->commit();
				$this->success("操作成功");
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
	* 添加
	*/
	public function add(){
		
		$re = $this->M->create($_POST, 1);
		if ($re) {
			$re['userInfo'] = M('userInfo')->create($_POST, 1);
			if ($this->M->relation('userInfo')->add($re)) {
				$this->success(L('ADD_SUCCESS'));
			} else {
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
		//移除老方法的编辑会改变密码
        /*$re = $this->M->create($_POST, 2);
		if ($re) {
			$re['userInfo'] = M('userInfo')->create($_POST);
			if ($this->M->relation('userInfo')->save($re) !== false) {
				$this->success(L('ADD_SUCCESS'));
			} else {
				$this->error($this->M->getError().$this->M->getLastSql());
			}
		} else {
			$this->error($this->M->getError().$this->M->getLastSql());
		}*/
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

	public function _before_delete() {
		$this->setQeuryCondition();
	}
}