<?php
namespace Home\Controller;

use Think\Controller;

class NavController extends Controller {
	public function index() {
		$nav = C('MENU');
		$this->assign("nav", $nav);
		$this->display();
	}

	public function test() {
		// $nav = C('MENU');
		$nav = [[
          "date"=> '2016-05-01',
          "name"=> '王小虎1',
          "address"=> '上1海市普陀区金沙江路 1518 弄'
        ]];
        $this->ajaxReturn( $nav );
	}
	
}