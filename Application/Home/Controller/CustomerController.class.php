<?php
namespace Home\Controller;

class CustomerController extends CommonController {
	protected $table = "customer";
	public function index () {
		// $dataList = $this->getList();
		$this->assign('customerType', $this->M->getType());
		$this->assign('sexType',      $this->M->getSexType());
		$this->display();
	}

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		$this->M->where(array("user_id"=> session('uid')));
	}

	/**
	* 在添加之前 加入一个参数
	* @return true
	*/
	public function _before_add(){
		$_POST['user_id'] = session('uid');
		return true;
	}


	/**
	* 在编辑之前 判断是不是 “我” 的客户
	* @return boolean
	*/
	public function _before_edit(){
		$id = I('post.id');
		if ($this->M->where(array('id'=>$id, 'user_id'=>session('uid') ))->field('id')->find()) {
			return true;
		} else {
			return false;
		}
	}
}