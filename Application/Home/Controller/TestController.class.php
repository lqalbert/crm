<?php
namespace Home\Controller;

class TestController extends CommonController {
	protected $table="department_basic";
	public function index() {
		$this->display();
	}
	function main() {
		$this->display();
	}
}