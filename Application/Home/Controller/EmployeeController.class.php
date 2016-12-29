<?php
namespace Home\Controller;

class EmployeeController extends CommonController {
	protected $table="rbac_user";


	public function index (){
		$this->assign("roleList", $this->getRoles());
		$this->display();
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
				header('HTTP/1.1 418 DIY_ERROR');
				$this->error("操作失败".$M->getError());
			}
		} else {
			$M->rollback();
			header('HTTP/1.1 418 DIY_ERROR');
			$this->error("操作失败".$M->getError());
		}
	}
}