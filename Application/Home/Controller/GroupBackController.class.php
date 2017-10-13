<?php
namespace Home\Controller;

use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\GroupModel;

class GroupController extends CommonController {
	protected $table="group_basic";
	protected $departmentSelect=NULL;

	
	public function index (){

		// 联系人列表
		$user = new User();
		$user->getRoleObject();
		/*$contactList = $user->getRoleGroupContacts();*/
		//上级组织
		$org = $user->getRoleGroupOrgs();

        //部门选项权限
		$ename=$this->getRoleEname();
		if($ename == RoleModel::GOLD || $ename==RoleModel::HR ||$ename==RoleModel::HR_MASTER)
		{
		    //总经办、人事经理、人事专员
            $departments=array('list'=>D('Department')->getAllDepartments('id,name'),'account'=>'','group'=>"");
        }else{
		    //部门经理
            //查询所在部门
		    $arr=D('Department')->where(array('id'=>session('account')['userInfo']['department_id']))->field('id,name')->select();
		    //部门所属团队小组
		    $ar=D('Group')->getAllGoups(session('account')['userInfo']['department_id'],'id,name');
		    $departments=array('list'=>$arr,'account'=>$arr,'group'=>$ar);
        }
		$this->assign("namelist",    $org);
		$this->assign("contactList", array());
		// $this->assign("memberList",  $members);
        $this->assign('departments', $departments);
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
		if (!empty(I('get.group_id'))) {
			$map['group_basic.id']=I('get.group_id');
		}
		if(isset($_GET['department_id'])){
            $map['group_basic.department_id']=$_GET['department_id'];
        }
        if(!empty(I('get.realname')))
        {
            $map['realname']=array('like',I('get.realname').'%');
        }
        if(!empty(I('get.phone')))
        {
            $map['ui.mphone']=array('like',I('get.phone').'%');
        }
		$map['group_basic.status'] = array('GT', GroupModel::DELETE_STATUS);
		$user = new User();
		$user->getRoleObject();
		$user->setGroupQueryCondition($this->M);	
		$this->M->where($map)
				->join('left join user_info as ui on group_basic.user_id = ui.user_id')
				->join('left join department_basic as db on group_basic.department_id = db.id')
				->field('group_basic.* ,ui.realname as realname, ui.mphone as phone, db.name as db_name');

	}

	public function _before_add(){
		$this->rightProcted();
		if (empty(I('post.user_id'))) {
			// unset($_POST['user_id']);
			$_POST['user_id'] = null;
			
		}
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
		if (empty($user_ids)) {
			$this->error("请选择成员");
		}
		$this->setGroupEmployee($user_ids, $id);
		
	}

	public function removeMember(){
		$user_ids = I("post.user_ids");
		$this->setGroupEmployee($user_ids, 0);
	}

	/**
    * 获取 对应的备选负责人
    */
	public function getUsers(){

		// 暂进没想到更好的方式了
		$id = I("get.id", 0, 'intval'); //user_id;
		$user = new User();
		$user->getRoleObject();
		$contactList = $user->getRoleGroupContacts($id);

		$this->ajaxReturn($contactList);
	}
	/***
	*获取所选部门所属的团队小组
     */
	public function getGroups(){
	    if(isset($_GET['department_id'])){
	        $arr=D('Group')->getAllGoups(I('get.department_id'),'id,name');
	        $this->ajaxReturn($arr);
        }
    }

    public function getGroupsByDepartmentId(){
		$department_id = I('get.department_id');
		$list = $this->M->where(array("department_id"=>$department_id))->select();

		$this->ajaxReturn($list);
        // echo json_decode($list);
	}

	public function _before_delete(){
    $ids = I("post.ids");
    
    foreach ($ids as $key => $value) {
      $sql = "update customers_basic set to_gid = 0 where to_gid =".$value." and salesman_id=0;";
      M()->execute($sql);
    }
    
  }


}
