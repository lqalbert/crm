<?php
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\RoleModel;

class DepartmentController extends CommonController {
	protected $table="Department";


	public function index(){
		$count = $this->M->count();
		$type = $this->M->getType();
		
		$this->assign("typeList", $type);
		$this->assign("totalCount", $count);
		$this->assign("zoneList", M('department_zone')->getField('id,name'));
		$this->display();
	}

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		$map = array('department_basic.status'=>array('EGT',0) ); //查询的参数
		if ( !empty(I('get.name')) ) {
			$map['name'] = array('like',"%". I('get.name')."%");
		}
		$this->M->where($map);

		$this->M->join(' department_division as dd on department_basic.division_id = dd.id')
		        ->join('left join user_info as ui on department_basic.user_id = ui.user_id ')
				->field("department_basic.*, ui.realname as contact ,ui.mphone as tel, dd.name as p_name");
	}


	public function getDivision(){
		$this->ajaxReturn( D('DepartmentDivision')->getAll('id,name') );
	}
    
    /**
    * 获取区域/部门 对应的备选负责人
    * 1 获取 区域
    * 2 获取 事业部门
    * 3 获取 推广部门
    */
	public function getUsers(){

		$RoleModle = D('Role');
		$departmentRoleId = $RoleModle->getIdByEname(RoleModel::DEPARTMENTMASTER);
		$id = I("get.id", 0);
		$sql = "select user_id,mid(realname, 1, 5) as realname from user_info where (role_id=$departmentRoleId and user_id not in(select user_id from department_basic where user_id is not null) ) or user_id=$id";
		$result = $this->M->query($sql);
		$this->ajaxReturn($result);
	}





}