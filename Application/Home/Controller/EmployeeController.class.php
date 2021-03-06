<?php
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\DepartmentModel;
use Home\Model\RoleModel;

class EmployeeController extends CommonController {
	protected $table="RbacUser";
	protected $pageSize = 13;


	public function index (){


		$this->assign("roleList", $this->getRoles());
		$this->assign("groupList", D('Group')->where(array('status'=>1))->select());
		$this->assign("sexType", array("未定义", "男", "女"));
		$ename = $this->getRoleEname();
    	$this->assign('viewDecorator', $this->M->decoratorView($ename));

    	//部门选项权限
    	$ename=$this->getRoleEname();

        //所属部门
        $arr=D('Department')->where(array('id'=>session('account')['userInfo']['department_id']))->field('id,name,type')->select();
    	if($ename==RoleModel::GOLD||$ename==RoleModel::HR||$ename==RoleModel::HR_MASTER){
    	    //总经办、人事经理、人事专员权限
            if($ename==RoleModel::HR&&$arr[0]['type']!='3'){
                $ar=D('Group')->getAllGoups(session('account')['userInfo']['department_id'],'id,name');
                $departments=array('list'=>$arr,'account'=>$arr,'group'=>$ar);
            }else{
                $departments=array('list'=>D('Department')->getAllDepartments('id,name'),'account'=>'','group'=>'');
            }
        }else{
    	    //部门经理权限
            //部门所属团队小组
            $ar=D('Group')->getAllGoups(session('account')['userInfo']['department_id'],'id,name');
            $departments=array('list'=>$arr,'account'=>$arr,'group'=>$ar);
        }
    	$this->assign('departments',$departments);
    	$this->assign('depart_id', $this->getDepartmentId());
    	$this->assign('departmentItem', $this->setEmployeeDepartemtnItem());
    	$this->assign('allRoles', D('Role')->getField('id,name', true));
		$this->display();
	}

	public function getList()
    {
        $result=$this->_getList();
        
        foreach ($result['list'] as $k=>$v){
            $result['list'][$k]['qq_nickname']=mb_substr($v['qq_nickname'],0,12);
            $result['list'][$k]['weixin_nickname']=mb_substr($v['weixin_nickname'],0,12);
        }
        if (IS_AJAX) {
            $this->ajaxReturn($result);
            // $this->ajaxReturn($this->M->getLastSql());
        }  else {

            return $result;
        }
    }

    public function setQeuryCondition() {

		// $this->M->relation(true)->field('password',true)->where(array('no_authorized'=>0));
		$this->M->join('user_info ON rbac_user.id = user_info.user_id')
				->join('department_basic as db on user_info.department_id=db.id', 'left')
		        ->field('db.name as department_name,account,user_info.address,
		        	area_city,area_district,
		        	area_province,created_at,
		        	department_id,group_id,
		        	head,rbac_user.id,mphone,no_authorized,phone,
		        	qq,qq_nickname,realname,role_ename,role_id,sex,rbac_user.status,user_info.user_id,weixin,
		        	weixin_nikname,id_card,card_img,card_front,card_back,ip,location,lg_time,out_time,creator')->where(array('no_authorized'=>0))
		        ->where(array('rbac_user.status'=>I('get.status')));

        if (isset($_GET['department_id'])) {
            $this->M->where(array('department_id'=>$_GET['department_id']));
        }

        if (isset($_GET['group_id'])) {
            $this->M->where(array('group_id'=>$_GET['group_id']));
        }
		/*$user = new User;
		$user->getRoleObject();
		$user->setEmployQueryCondition($this->M);*/
		$this->setRoleCondition();
		if (isset($_GET['type']) && isset($_GET['typeValue'])) {
			$this->M->where(array(I('get.type')=>array('like', I('get.typeValue')."%")));
		}	
		$this->M->where(array('rbac_user.id'=>array('neq', session('uid'))));


	}

	private function getDepartmentRow(){
		if (!isset($this->departmentRow)) {
			$this->departmentRow = D('Department')->find($this->getDepartmentId());
		}
		return $this->departmentRow;
	}

	private function isHrDeparment(){
		$this->getDepartmentRow();
		
		if ($this->departmentRow['type'] == DepartmentModel::HR_DEPARTMENT) {
			return true; //$_POST['department_id'] = 0;
		} else {
			return false; //$_POST['department_id'] = session('account')['userInfo']['department_id'];
		}
	}

	private function getDepartmentId(){
		// var_dump(session('account')['userInfo']['department_id']);
		if ($this->getRoleEname()=='gold') {
			return 0;
		} else {
			$depart_id = session('account')['userInfo']['department_id'];
			
			if ($depart_id == 0) {
				return 9999999;
			} else {
				return $depart_id;
			}
		}
		
	}

	

	private function setDeparmentQuery(){
		$departmentRow = $this->getDepartmentRow();
		$config = json_decode($departmentRow['config'], true);
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
	// 
	private function goldCondition(){
		$this->setAllEmployee();
	}

	//人事
	private function humanResourceCondition(){

		if ($this->isHrDeparment()) {
			$this->setAllEmployee();
		} else {
			$this->setDeparmentQuery();
		}
		
	}

	//人事经理
	private function hrMasterCondition(){
		$this->setAllEmployee();
	}

	//风控经理
	private function riskMasterCondition(){
		$this->setDepartmentEmployee();
	}
	//客服经理
	private function serviceMasterCondition(){
		$this->setDepartmentEmployee();
	}



	//部门经理
	private function departmentMasterCondition(){
		$this->setDepartmentEmployee();
	}

	//推广部经理
	private function spreadMasterCondition(){
		$this->setDepartmentEmployee();
	}

	//推广部经理
	private function counselorMasterCondition(){
		$this->setDepartmentEmployee();
	}


	public function setRoleCondition(){
		$this->roleEname = $this->getRoleEname();
        $funcName = $this->roleEname."Condition";
        if (method_exists($this, $funcName)) {
             call_user_func(array($this, $funcName));
        } else {
        	$this->error("没有权限EmployeeController");
        }
	}

	



	public function getRoles(){
		/*$row = M('rbac_role')->field('level')->find(session('account')['userInfo']['role_id']);
		return D('rbac_role')->where(array('level'=>array('gt', $row['level'])))->select();*/
		$departRow = D("Department")->find(session('account')['userInfo']['department_id']);
		if ($departRow) {
			return D("Department")->getEmployeeRoles($departRow['type']);
		} else {
			return D('rbac_role')->select();
		}
		
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
		/*$user = new User;
		$user->getRoleObject();
		$user->setEmployeeAddData();*/

		//如果是人事部 则添加的员工部门为 0
		//如果不是人事部 则添加的员工部门为 当前员工的部门


		// $departmentRow = D('Department')->find($this->getDepartmentId());
		/*if ($this->isHrDeparment()) {
			$_POST['department_id'] = 0;
		} else {
			$_POST['department_id'] = session('account')['userInfo']['department_id'];
		}*/

		
	}

	/**
	* 添加
	*/
	public function add(){
        
		$re = $this->M->create($_POST, 1);
		if ($re) {
			$this->M->startTrans(); 
			$this->setCreator();
			$re['userInfo'] = M('userInfo')->create($_POST, 1);
			if (empty($re['userInfo']['head'])) {
				unset($re['userInfo']['head']);
			}
			$id = $this->M->relation('userInfo')->add($re);
			if ($id) {
				$role_list = array('role_id'=>$re['userInfo']['role_id'], 'user_id'=>$id);
				if (M('rbac_role_user')->add($role_list)) {
					// 
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

	public function setCreator(){
		$user = M("user_info")->where(array("user_id"=>session("uid")))->field("realname,department_id")->find();
		if ($user) {
			$user['depart_name'] = D("Department")->where(array("id"=>$user['department_id']))->getField('name');
			if ($user['depart_name']) {
				$_POST['creator'] = $user['depart_name'] ."-".$user['realname'];
			} else {
				$_POST['creator'] = $user['realname'];
			}
		}

	}

	/**
	* 编辑
	*/
	public function edit(){
		//新方法
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

	public function setEmployeeDepartemtnItem(){
		if ($this->getRoleEname()=='gold') {
			return 1;
		} else if(session('account')['userInfo']['department_id']!=0){
			$row = $this->getDepartmentRow();
			if ($row['type']==DepartmentModel::HR_DEPARTMENT) {
				return 1;
			} 
		}
		return 0;
	}

	/*public function _before_delete() {
		$this->setQeuryCondition();
	}*/
	/**
	*获取所选部门所包含小组
     */
	public function getAllGroups(){
		$id =I('get.department_id',-1);
		empty($id) && $id = -1;
	    $arr=D('Group')->getAllGoups($id,'id,name');
        $this->ajaxReturn($arr);
    }
}
