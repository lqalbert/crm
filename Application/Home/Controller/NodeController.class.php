<?php
namespace Home\Controller;

class NodeController extends CommonController{

	protected $table = "rbac_node";

	public function index(){
		
		$this->setQeuryCondition();
		// $this->pageSize = $this->M->count();
		$this->assign('pageSize', $this->M->count());

		$this->display();
	}

	/**
	 * 公用 设置参数
	 * 子类
	 * @return  null
	 * 
	 **/
	public function setQeuryCondition() {

		$this->M->setFilterLevelOne()->order("sort asc");
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
		$list = $this->M->select();
		$result = array('list'=>$this->M->reSort($list), 'count'=>$count);
		if (IS_AJAX) {
			$this->ajaxReturn($result);
		}  else {
			return $result;
		}

	}

	


		
}