<?php
namespace Home\Controller;

class DepartmentController extends CommonController {
	protected $table="department_basic";

	public function index(){
		$this->assign("dataSource", $this->getList());
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
}