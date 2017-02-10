<?php
namespace Home\Controller;
use Common\Lib\User;
class GroupController extends CommonController {
	protected $table="group_basic";

	
	public function index (){
		// $namelist=M('department_basic')->field('name,zone')->select();

		// 联系人列表
		$user = new User();
		$user->getRoleObject();
		$contactList = $user->getRoleGroupContacts();

		//上级组织
		$org = $user->getRoleGroupOrgs();


		$this->assign("namelist",    $org);
		$this->assign("contactList", $contactList);
		$this->display();
	}

	public function setQeuryCondition(){
		$map=array();
		if (!empty(I('get.name'))) {
			$map['name']=array('like',"%".I('get.name')."%");
		}
		
		$this->M->where($map);
	}


	


	public function _before_add(){
		$user_id = I('post.user_id',0);
		if (!empty($user_id)) {
			$this->setContactPost($user_id);
		}
		return true;
	}


	public function _before_edit(){
		$user_id = I('post.user_id',0);
		$id = I('post.id');
		$old_row = $this->M->find($id);
		if ($old_row['user_id'] != $user_id) {
			$this->setContactPost($user_id);
		}

		
		return true;
	}


}