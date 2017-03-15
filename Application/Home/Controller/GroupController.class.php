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

		//所有队员
		// $members = $user->getAllBenC();
		
		$this->assign("namelist",    $org);
		$this->assign("contactList", $contactList);
		// $this->assign("memberList",  $members);
		$this->display();
	}

	public function getMemberList(){
		$user = new User();
		$user->getRoleObject();
		$members = $user->getAllBenC();
		$this->ajaxReturn($members);
	}




	public function setQeuryCondition(){
		$map=array();
		if (!empty(I('get.name'))) {
			$map['name']=array('like',"%".I('get.name')."%");
		}

		$user = new User();
		$user->getRoleObject();
		$contactList = $user->setGroupQueryCondition($this->M);
		
		$this->M->where($map);
	}

	public function _before_add(){
		$this->rightProcted();


		$user_id = I('post.user_id', null);
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

	public function getEmployeesByGroupId(){
		$re = M('user_info')->where(array('group_id'=>I('get.id')))->field('user_id,qq,realname,mphone as phone')->select();
		$this->ajaxReturn($re);
	}

	private function setGroupEmployee($user_ids , $group_id){
		
		$re = M('user_info')->where(array('user_id'=>array('in', $user_ids)))->data(array('group_id'=>$group_id))->save();
		if ($re) {
			$this->success("操作成功");
		} else {
			$this->error('操作失败');
		}
	}

	public function setEmployees(){
		$id = I("post.id");
		$user_ids = I("post.user_ids");
		$this->setGroupEmployee($user_ids, $id);
		
	}

	public function removeMember(){
		$user_ids = I("post.user_ids");
		$this->setGroupEmployee($user_ids, 0);
	}


}