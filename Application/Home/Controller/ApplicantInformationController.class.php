<?php
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\RbacUserModel;

class ApplicantInformationController extends CommonController {
	protected $table="RbacUser";
	protected $pageSize = 13;


	public function index (){
		$D = D('RbacUser');
		$this->assign('employeeStatus', $D->getStatusType());
		$this->assign("roleList", $this->getRoles());
		$this->assign("groupList", D('Group')->where(array('status'=>1))->select());
		$this->assign("sexType", array("未定义", "男", "女"));
		$this->assign("marriageType", array("未定义", "未婚", "已婚"));
		$this->assign("sourcesList", $D->getSources());
		/*$this->assign("sourcesList", $this->getSources());*/
		$allProvinces = D('AreaInfo')->where("p_id=1")->order("id asc")->select();
		$provinces_arr = array();
		foreach($allProvinces as $k=>$v){
			$provinces_arr[$v["id"]] = $v["name"];
		}
		foreach($allProvinces as $k=>$v){
			$provinces_id[] = $v["id"];
		}
		$where["p_id"] =array('in',$provinces_id);
		$allCities = D('AreaInfo')->where($where)->order("id asc")->select();
		foreach($allCities as $kk=>$vv){
			$cities_arr[$vv["id"]] = $vv["name"];
			$cities_id[] = $vv["id"];
		}
		$where2["p_id"] = array('in',$cities_id);
		$allDistricts = D('AreaInfo')->where($where2)->order("id asc")->select();
		foreach($allDistricts as $kkk=>$vvv){
			$districts_arr[$vvv["id"]] = $vvv["name"];
		}
		$this->assign("allProvinces", $provinces_arr);
		$this->assign("allCities", $cities_arr);
		$this->assign("allDistricts", $districts_arr);
		$this->display();
	}

	public function setQeuryCondition() {
		$this->M->field('name,sex,id_num,mphone,qq,weixin,graduation_date,university,major,position,info_sources,resume_enterprise_name,resume_entry_date,resume_position,resume_leader,resume_leader_phone,resume_leaving_reasons,interviewer_name,interviewer_department,interviewer_mphone,interviewer_qq,interviewer_opinion,interviewer_date')->order("id desc");
		$this->setRoleCondition();

		if (isset($_GET['name'])) {
			$this->M->where(array('account'=>array('like', I('get.name')."%")));
		}
		//员工在职或离职
		 $status = I('get.status') ;
        if(isset($status)){
        	switch($status){
        		case 1:
        		$this->M->where(array('rbac_user.status'=>array('EGT',0)));
        		break;
        		case 2:
        		$this->M->where(array('rbac_user.status'=>array('LT',0)));
        		break;
        	}
        }
	}

	private function getDepartmentId(){
		return session('account')['userInfo']['department_id'];
	}

	private function goldCondition(){
		$this->setAllEmployee();
	}

	private function setDeparmentQuery(){
		$departmentRow = D('Department')->find($this->getDepartmentId());
		$config = json_decode($departmentRow['config']);
		if (isset($config['EmployeeQueryCondition'])) {
			call_user_func(array($this, 'set'.$config['EmployeeQueryCondition']));
		}
	}



	private function setDepartmentEmployee(){
		$this->M->where(array(
			'user_info.department_id'=>array('eq', $this->getDepartmentId()),
			// 'role_id'=>array('NEQ', array()) 
		));
	}
	private function setAllEmployee(){
		
	}

	//人事
	private function humanResourceCondition(){
		$this->setDeparmentQuery();
	}



	//部门经理
	private function departmentMasterCondition(){
		$this->setDeparmentQuery();
	}


	public function setRoleCondition($M){
		$this->roleEname = $this->getRoleEname();
        $funcName = $this->roleEname."Condition";
        if (method_exists($this, $funcName)) {
             call_user_func(array($this, $funcName));
        } else {
        	$this->error("没有权限");
        }
	}

	



	public function getRoles(){
		$row = M('rbac_role')->field('level')->find(session('account')['userInfo']['role_id']);
		return D('rbac_role')->where(array('level'=>array('gt', $row['level'])))->select();
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
		if (is_array($role_ids)) {
			foreach ($role_ids as $value) {
				$insert_list[] = array('role_id'=>$value, 'user_id'=>$user_id);
			}
		} else {
			$insert_list[] = array('role_id'=>$role_ids, 'user_id'=>$user_id);
		}
		
		$M->startTrans(); 
		$result = $M->where(array('user_id'=>$user_id))->delete();

		if ($result !== false) {
			$insert_result = $M->addAll($insert_list);
			if ($insert_result !== false) {
				$re = M('user_info')->data(array('user_id'=>$user_id, 'role_id'=>$role_ids))->save();
				if ($re !== false) {
					$M->commit();
					$this->success("操作成功");
				} else {
					$M->rollback();
					$this->error("操作失败".M('user_info')->getError());
				}
				
			} else {
				$M->rollback();
				$this->error("操作失败".$M->getError());
			}
		} else {
			$M->rollback();
			
			$this->error("操作失败".$M->getError());
		}
	}

	/**
	* 预处理
	*/
	public function _before_add(){
		$this->rightProcted();
		$user = new User;
		$user->getRoleObject();
		$user->setEmployeeAddData();


	}

	/**
	* 添加
	*/
	public function add(){
        
		$re = $this->M->create($_POST, 1);
		if ($re) {
			$this->M->startTrans(); 
			$re['userInfo'] = M('userInfo')->create($_POST, 1);
			if (empty($re['userInfo']['head'])) {
				unset($re['userInfo']['head']);
			}
			$id = $this->M->relation('userInfo')->add($re);
			if ($id) {
				$role_list = array('role_id'=>$re['userInfo']['role_id'], 'user_id'=>$id);
				if (M('rbac_role_user')->add($role_list)) {
					$this->M->commit();
					$this->success(L('ADD_SUCCESS'));
				} else {
					$this->M->rollback();
					$this->error(M('rbac_role_user')->getError());
				}
			} else {
				$this->M->rollback();
				$this->error($this->M->getError());
			}
		} else {
			$this->error($this->M->getError());
		}

	}

	/**
	* 编辑
	*/
	public function edit(){
		//新方法
		/*echo 'ss';
		print_r($_POST);
		echo 'gg';*/
		$_POST["birth_date"] = UTC_to_locale_time($_POST["birth_date"]);
		$_POST["graduation_date"] = UTC_to_locale_time($_POST["graduation_date"]);
		$_POST["entry_date"] = UTC_to_locale_time($_POST["entry_date"]);
		$_POST["completion_date"] = UTC_to_locale_time($_POST["completion_date"]);
		$_POST["resume_entry_date"] = UTC_to_locale_time($_POST["resume_entry_date"]);
        $re = M('userInfo')->create($_POST, 2);
		if ($re) {
			if (M('userInfo')->where(array('user_id'=>I('post.id') ))->save() !== false) {
				$this->success(L('ADD_SUCCESS'));
			} else {
				$this->error($this->M->getError().$this->M->getLastSql());
			}
		} else {
			$this->error($this->M->getError().$this->M->getLastSql());
		}

	}


	public function changePassword(){
		$re = $this->M->create($_POST, 2);
		if ($re) {
			if ($this->M->save() !== false) {
				$this->success(L('ADD_SUCCESS'));
			} else {
				$this->error($this->M->getError().$this->M->getLastSql());
			}
		} else {
			$this->error($this->M->getError().$this->M->getLastSql());
		}
	}

	/*public function _before_delete() {
		$this->setQeuryCondition();
	}*/
}
