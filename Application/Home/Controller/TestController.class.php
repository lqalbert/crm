<?php
namespace Home\Controller;

class TestController extends CommonController {
	protected $table="department_basic";
	protected $pageSize = 1;

	public function index() {
		// $this->assign('pageSize', $this->pageSize);
		$this->display();
	}


	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		$map = array(); //查询的参数
		$map['name'] = array('like', I('get.name')."%");
		$this->M->where($map);
	}




	
	function main() {
		$this->display();
	}
}