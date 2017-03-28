<?php
namespace Home\Controller;
use Common\Lib\User;

class EmployeeController extends CommonController {
	protected $table="RbacUser";
	protected $pageSize = 13;


	public function index (){
		$this->assign("roleList", $this->getRoles());
		$this->assign("groupList", D('Group')->where(array('status'=>1))->select());
		$this->assign("sexType", array("未定义", "男", "女"));
		$this->display();
	}

	public function setQeuryCondition() {
		// $this->M->relation(true)->field('password',true)->where(array('no_authorized'=>0));
		$this->M->join('user_info ON rbac_user.id = user_info.user_id')
		        ->field('account,address,
		        	area_city,area_district,
		        	area_province,created_at,
		        	department_id,group_id,
		        	head,id,mphone,no_authorized,phone,
		        	qq,qq_nickname,realname,role_ename,role_id,sex,status,user_id,weixin,weixin_nikname,id_card,card_img,card_front,card_back')->where(array('no_authorized'=>0))
		        ->where(array('rbac_user.status'=>array('EGT',0)));

		$user = new User;
		$user->getRoleObject();
		$user->setEmployQueryCondition($this->M);

		if (isset($_GET['name'])) {
			$this->M->where(array('account'=>array('like', I('get.name')."%")));
		}


	}

	public function getRoles(){
		$row = M('rbac_role')->field('level')->find(session('account')['userInfo']['role_id']);
		return D('rbac_role')->where(array('level'=>array('gt', $row['level'])))->select();

		/*$user = new User;
		$user->getRoleObject();
		return $user->getEmployeeRoleList();*/


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