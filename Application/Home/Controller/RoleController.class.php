<?php
namespace Home\Controller;

use Think\Model;

/**
*
* todo 建立模型 数据验证
*/
class RoleController extends CommonController{

	function index(){
		$roleList = M("rbac_role")->field("id,name,remark,status,pid")->where('status>=0')->select();
		$this->assign("roleList", $roleList);
		$this->display();
	}


	public function addRole() {

		if (M('rbac_role')->create($_POST, Model::MODEL_INSERT) && M('rbac_role')->add()) {
			$this->success(L('ADD_SUCCESS'));
		} else {
			$this->error(L('ADD_ERROR').M('rbac_role')->getError());
		}
	}


	public function editRole() {
		if (M('rbac_role')->create($_POST, Model::MODEL_UPDATE) && M('rbac_role')->save()) {
			$this->success(L('EDIT_SUCCESS'));
		} else {
			$this->error(L('EDIT_ERROR'));
		}
	}


	public function deleteRole() {
		if (M('rbac_role')->create($_POST, Model::MODEL_UPDATE)->save()) {
			$this->success(L('DELETE_SUCCESS'));
		} else {
			$this->error(L('DELETE_ERROR'));
		}
	}
}





































?>