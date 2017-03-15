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
			$map['group_basic.name']=array('like', I('get.name')."%");
		}

		$user = new User();
		$user->getRoleObject();
		$user->setGroupQueryCondition($this->M);
		
		$this->M->where($map)
				->join('left join user_info as ui on group_basic.user_id = ui.user_id')
				->field('group_basic.* , ui.realname as realname, ui.mphone as phone');
	}

	public function _before_add(){
		$this->rightProcted();
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