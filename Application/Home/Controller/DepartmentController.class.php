<?php
namespace Home\Controller;

class DepartmentController extends CommonController {
	protected $table="department_basic";

	public function index(){
		$this->_before_getList();
		$count = $this->M->count();
		$this->_before_getList();
		$this->assign("datalist", $this->getList());
		$this->assign("totalCount", $count);
		$this->display();
	}

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function _before_getList() {
		$map = array(); //查询的参数
		$this->M->where($map);
	}
}