<?php
namespace Home\Controller;

class DepartmentController extends CommonController {
	protected $table="department";

	public function index(){
		$this->_before_getList();
		$count = $this->M->count();
		$this->_before_getList();
		$datalist=$this->getList();
		$namelist=$this->M->field("name,zone")->where(array("p_id"=>0))->select();
		$this->assign("datalist", $datalist);
		$this->assign("totalCount", $count);
		$this->assign("namelist",$namelist);

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