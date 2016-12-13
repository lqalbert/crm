<?php
namespace Home\Controller;

use Think\Controller;

class CustomerController extends Controller {
	public function index () {
		// $dataList = $this->getList();

		$this->assign("datalist", $this->getList());
		$this->display();
	}


	private function getList(){
		if (IS_AJAX) {
			$this->ajaxReturn();
		}  else {
			return [];
		}
	}

	public function add(){

	}

	public function update(){

	}

	public function delete(){
		
	}
}