<?php
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\RoleModel;

class DepartmentController extends CommonController {
	protected $table="Department";

	

	public function index(){
		$count = $this->M->count();
		$user = new User();
		$RoleModle = D('Role');
		$departmentRoleId = $RoleModle->getIdByEname(RoleModel::DEPARTMENTMASTER);

		$type = $this->M->getType();
		unset($type[0]);// 一定要用unset array_shift 会重建索引
		$this->assign("typeList", $type);
		// $this->assign("datalist", $datalist);
		$this->assign("totalCount", $count);
		$this->assign("zoneList", M('department_zone')->getField('id,name'));
		$this->assign("departmentMaster", M('user_info')->where(array('role_id'=>$departmentRoleId ))->field('user_id,realname')->select());

		$this->display();
	}

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		$map = array(); //查询的参数
		// $map['name'] = array('like',"%". I('get.name')."%");
		if ( !empty(I('get.name')) ) {
			$map['name'] = array('like',"%". I('get.name')."%");
		}
		$map['id'] = array('neq',0);
		$map['p_id'] = array('eq', I('get.id',0));
		$this->M->where($map);
	}


	public function getList(){
		$result  = $this->_getList();
		$list = $result['list'];
		foreach ($list as $key => $value) {
			$list[$key]['children'] = $this->M->where("p_id=" . $value['id'])->select();
		}
		$result['list'] = $list;
		$this->ajaxReturn($result);
	}

	public function getTopD(){
		// $this->M->where(array('type'=>array('EQ', I('get.type')-1)));
		$type = I('get.type');
		switch ($type) {
			case '1':
				$this->M->field('id,name,level')->where(array('type'=>array('EQ', $type-1)));
				break;
			case '2':
				$this->M->field('id,name,level')->where(array('type'=>array('LT', $type)));
				break;
			case '3':
				$this->M->field('id,name,level')->where(array('type'=>array('LT', $type)));
				break;
			default:
				#
				break;
		}
		$this->ajaxReturn(
			$this->M->field('id,name,level')->order('level asc ,id asc')->select()
		);
	}

	/**
	* 用user_id 得到 phone realname
	*/
	public function _before_add(){
		$user_id  = I("post.user_id", 0);
		if (!empty($user_id)) {
			 $this->setContactPost($user_id);
		}
		return true;
	}

    
	/**
	* 在编辑之前 处理
	* @return true
	*/
	public function _before_edit(){
		$id  = I('post.id');
		$old = $this->M->field('name,user_id')->find($id);
		$old_name = $old['name'];
		$new_name = I("post.name");
		if ($old_name != $new_name) {
			$re = $this->M->where(array('p_id'=>$id))->save(array('p_name'=>$new_name));
			if ($re !== false) {
				return true;
			} else {
				return false;
			}
		}

		//如果改了负责人
		$userid = I('post.user_id');
		if ($old['user_id'] != $userid) {
			 $this->setContactPost($userid);
		}
		return true;
	}
    
    /**
    * 获取区域/部门 对应的备选负责人
    * 1 获取 区域
    * 2 获取 事业部门
    * 3 获取 推广部门
    */
	public function getUsers(){
		$type = I("get.type");
		$role_id = D("role")->getIdByType($type);
		$sql  = "select user_id from rbac_role_user where role_id = $role_id";
		$sql  = "select user_id, realname, mphone from user_info where user_id in($sql)";
		$result = $this->M->query($sql);
		$this->ajaxReturn($result);

	}

}