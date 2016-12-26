<?php
namespace Home\Controller;

use Think\Controller;
use Org\Util\Rbac;
use Think\Model;

/**
*  继承一个公共的父类
*/
class CommonController extends Controller {
	protected $pageSize = 10;

	protected $M = null;

	/**
	*
	*/
	public function _initialize() {
		if (!session("uid")) {
			$this->redirect('Login/index');
		}

		Rbac::AccessDecision() || $this->error(L('NO_AUTHORIZED'));

		$this->parseJsonParams();

		$this->M = D($this->table);

		
	}



	/**
	* vue-resource 传递过来的是一个 json 格式的字符串
	* 这里就把这个转成 数组并赋值给 $_POST
	*/
	private function parseJsonParams(){
		
		if (IS_POST && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false  ) {
			$input = file_get_contents("php://input");
			$_POST = json_decode($input, true);
		}
	}




	/**
	 * 公用 添加
	 *
	 * @return void
	 * 
	 **/
	public function add() {
		if ($this->M->create($_POST, Model::MODEL_INSERT) && D($this->table)->add()) {
			$this->success(L('ADD_SUCCESS'));
		} else {
			$this->error(L('ADD_ERROR').D($this->table)->getError());
		}
	}


	/**
	 * 公用 编辑
	 *
	 * @return void
	 * 
	 **/
	public function edit() {
		if ($this->M->create($_POST, Model::MODEL_UPDATE) && D($this->table)->save()) {
			$this->success(L('EDIT_SUCCESS'));
		} else {
			$this->error(L('EDIT_ERROR'));
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
			$this->error(L('DELETE_ERROR'));
		}
	}



	/**
	 * 公用 获取列表
	 *
	 * @return array() || null
	 * 
	 **/
	public function getList(){
		
		$this->setQeuryCondition();
		$count = (int)$this->M->count();
		$this->setQeuryCondition();
		$list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();
		$result = array('list'=>$list, 'count'=>$count);
		if (IS_AJAX) {
			$this->ajaxReturn($result);
		}  else {
			$this->assign("pageSize", $this->pageSize);
			return $result;
		}

	}



}