<?php
namespace Home\Controller;
use Think\Controller;
class GroupController extends CommonController {
	protected $table="group";

	
	public function index (){
		$namelist=M('department_basic')->field('name,zone')->select();
		$this->assign("namelist",$namelist);
		$this->display();
	}
}