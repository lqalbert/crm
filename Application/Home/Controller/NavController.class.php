<?php
namespace Home\Controller;

class NavController extends CommonController {
	public function index() {
		$nav = C('MENU');
		$this->transNavUrl($nav);
		$this->assign("nav", $nav);
		$this->display();
	}


	private function transNavUrl(&$nav) {
		foreach ($nav as $key => $value) {
			
			if (isset($value['href'])) {
				$nav[$key]['href'] = $this->makeUrl($value['href']);
			}

			if (isset($value['children'])) {
				$this->transNavUrl($nav[$key]['children']);
			} 
		}
	}

	private function makeUrl($url) {
		return  strpos($url, "javascript:;") === false ? U($url) : $url ;
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