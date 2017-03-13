<?php
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\RoleModel;

class DepartmentController extends CommonController {
	protected $table="Department";

	

	public function index(){
		$count = $this->M->count();
		// $user = new User();
		$RoleModle = D('Role');
		$departmentRoleId = $RoleModle->getIdByEname(RoleModel::DEPARTMENTMASTER);

		$type = $this->M->getType();

		// var_dump($type);

		$this->assign("typeList", $type);
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
			$map['department_basic.name'] = array('like',"%". I('get.name')."%");
		}
		//$map['p_id'] = array('eq', I('get.id',0));
		$this->M->where($map);
		$this->M->join("left join department_division as dd on department_basic.division_id = dd.id")
		        ->join('left join user_info as ui on department_basic.user_id = ui.user_id')
		        ->field('department_basic.*,dd.name as p_name, ui.realname , ui.mphone as phone');
	}



	public function getDivision(){
		$this->ajaxReturn( D('DepartmentDivision')->getAll('id,name') );
	}

    
    /**
    * 获取区域/部门 对应的备选负责人 del
    * 1 获取 区域
    * 2 获取 事业部门
    * 3 获取 推广部门
    */
	public function getUsers(){
		/*$type = I("get.type");
		$role_id = D("role")->getIdByType($type);
		$sql  = "select user_id from rbac_role_user where role_id = $role_id";
		$sql  = "select user_id, realname, mphone from user_info where user_id in($sql)";
		$result = $this->M->query($sql);
		$this->ajaxReturn($result);*/

	}

}