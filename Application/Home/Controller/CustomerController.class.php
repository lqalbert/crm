<?php
namespace Home\Controller;

class CustomerController extends CommonController {
	public function index () {
		// $dataList = $this->getList();

		$this->assign("datalist", array());
		$this->display();
	}


	public function getList(){
		$list = D($this->table)->page(I('get.p',0). ','. $this->pageSize)->select();
		if (IS_AJAX) {
			$this->ajaxReturn($list);
		}  else {
			return $list !== false ? $list : array();
		}
	}

	public function add(){

	}

	public function update(){

	}

	public function delete(){
		
	}
}