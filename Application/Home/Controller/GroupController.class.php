<?php
namespace Home\Controller;
use Think\Controller;
class GroupController extends CommonController {
	protected $table="group_basic";

	
	public function index (){
		$namelist=M('department_basic')->field('name,zone')->select();
		$this->assign("namelist",$namelist);
		$this->display();
	}

	public function setQeuryCondition(){
		$map=array();
		$map['name']=array('like',"%".I('get.name')."%");
		$this->M->where($map);
	}
}