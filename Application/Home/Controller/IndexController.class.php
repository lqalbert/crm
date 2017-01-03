<?php
namespace Home\Controller;

class IndexController extends CommonController {
	public function index() {
		$this->display ();
	}

	public function main() {
		$this->assign("pageSize", 0);
		$this->display ();
	}

	
	public function getList(){
		$this->ajaxReturn(array());
	}
}