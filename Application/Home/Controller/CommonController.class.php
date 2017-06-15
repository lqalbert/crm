<?php
namespace Home\Controller;

use Think\Controller;
use Org\Util\Rbac;
use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
/**
*  继承一个公共的父类
*/
class CommonController extends Controller {
	protected $pageSize = 15;

	protected $M = null;

	/**
	*
	*/
	public function _initialize() {
		if (!session("uid")) {
			$this->redirect('Login/logOut');
		}
    if(!APP_DEBUG){
		  $re=D('rbac_user')->where(array('id'=>session('uid')))->getField('ss_id');
	    if($re!=session('ssid')){
	    	M('rbac_user')->where(array('id'=>session('uid')))->save(array('out_time'=>time()));
	    	session('[destroy]');
	    	$this->redirect('Login/index');
	    }
    }
    
		Rbac::AccessDecision() || $this->error(L('NO_AUTHORIZED'));

		$this->parseJsonParams();

		$this->M = D($this->table);

		\Think\Hook::add(HOOK_PRECHECK,'Home\\Behaviors\\precheckBehavior');
		\Think\Hook::add(HOOK_ADDCONTACT,'Home\\Behaviors\\checkContactBehavior');
		\Think\Hook::add(HOOK_DISTRIBUTE_BUY_CUSTOMER,'Home\\Behaviors\\disBuyCustomerBehavior');
		\Think\Hook::add(HOOK_CHECK,'Home\\Behaviors\\checkBehavior');

		
	}

	/**
	* 设置一个错误header
	* @return void
	*/
	protected function setErrorHeader(){
		header('HTTP/1.1 418 DIY_ERROR');
	}

	/**
	* 重载父类的error
	* $message = '', $jumpUrl = '', $ajax =
	*/ 
	public function error($message='',$jumpUrl='',$ajax=false){
		$this->setErrorHeader();
		parent::error($message,$jumpUrl,$ajax);
	}



	/**
	* vue-resource 传递过来的是一个 json 格式的字符串
	* 这里就把这个转成 数组并赋值给 $_POST
	*/
	private function parseJsonParams(){
		
		if (IS_POST && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false  ) {
			$_POST = json_decode(file_get_contents("php://input"), true);
			
		}
	}




	/**
	 * 公用 添加
	 *
	 * @return void
	 * 
	 **/
	public function add() {	
		if (!empty($_POST) && $this->M->create($_POST, Model::MODEL_INSERT) && $this->M->add()) {
			$this->success(L('ADD_SUCCESS'));
		} else {
			$this->error($this->M->getError());
		}
	}


	/**
	 * 公用 编辑
	 *
	 * @return void
	 * 
	 **/
	public function edit() {
		if ($this->M->create($_POST, Model::MODEL_UPDATE) && ($this->M->save() !== false) )  {
			$this->success(L('EDIT_SUCCESS'));
		} else {
			$this->error($this->M->getError());
		}
	}


	/**
	 * 公用 删除
	 *
	 * @return void
	 * 
	 **/
	public function delete() {
		if ($this->M->delete(implode(",", I("post.ids")) )) {
			$this->success(L('DELETE_SUCCESS'));
		} else {
			$this->error(L('DELETE_ERROR').$this->M->getError());
		}
	}


	/**
	 * 公用 设置参数
	 * 子类
	 * @return  null
	 * 
	 **/
	public function setQeuryCondition() {
		
	}

	protected function _getList(){

		$this->setQeuryCondition();

		$count = (int)$this->M->count();
		$this->setQeuryCondition();

		$list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
		// var_dump($this->M->getlastsql());
		$result = array('list'=>$list, 'count'=>$count);
		
		return $result;
	}

	/**
	 * 公用 获取列表
	 *
	 * @return array() || null
	 * 
	 **/
	public function getList(){

		$result = $this->_getList();
	  //echo $this->M->getLastSql();
		if (IS_AJAX) {
			$this->ajaxReturn($result);
			// $this->ajaxReturn($this->M->getLastSql());
		}  else {
			
			return $result;
		}

	}


	public function _before_index(){
		
		$this->assign("pageSize", $this->pageSize);
	}


	protected function setContactPost($user_id){
		 $row = M('user_info')->field('mphone,realname')->find($user_id);
		 $_POST['contact'] = $row['realname'];
		 $_POST['tel']     = $row['mphone'];
	}

	/**
	*   是不是 部门经理
	*  是 团队、员工不能添加
	*/
	protected function rightProcted(){
		/*$user = new User;
		$role_row = $user->getRole();
		if ($role_row['ename'] == RoleModel::DEPARTMENTMASTER) {
			$depart = D('department')->where(array('user_id'=>session('uid')))->find();
			if (!$depart) {
				$this->error("还没有分配部门给你，暂时不能添加");
			}
		}*/
		$rolename = $this->getRoleEname();
		if ($rolename!='gold') {
			if (session('account')['userInfo']['department_id']==0) {
				$this->error("还没有分配部门给你，暂时不能添加");
			}
		}

		
	}

	/**
	* 得到ename
	* 
	*/
	protected function getRoleEname(){
		if (!isset($this->_roleEname)) {
			$this->_roleEname = (new RoleModel)->getEnameById(session('account')['userInfo']['role_id']);
		}
		return $this->_roleEname ;
	}


}