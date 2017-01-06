<?php
namespace Home\Controller;

class DepartmentController extends CommonController {
	protected $table="department_basic";

	public function index(){
		
		$count = $this->M->count();
		
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
		$map['name'] = array('like',"%". I('get.name')."%");
		$this->M->where($map);
	}

	public function getTopD(){
		$this->ajaxReturn(
			$this->M->where(array('p_id'=>0))->field('id,name')->select()
		);
	}

}