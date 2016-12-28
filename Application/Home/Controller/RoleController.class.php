<?php
namespace Home\Controller;

use Think\Model;

/**
*
* todo 建立模型 数据验证
*/
class RoleController extends CommonController{
	protected $table = "rbac_role";


	function index(){
		
		$this->assign('pageSize', $this->M->count());
		$this->display();
	}

	public function _before_getList(){
		$this->pageSize = $this->M->count();
	}
}





































?>