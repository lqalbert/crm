<?php
namespace Home\Controller;

class CustomerController extends CommonController {
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