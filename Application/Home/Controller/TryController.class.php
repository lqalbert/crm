<?php
namespace Home\Controller;

use Think\Controller;
use Org\Util\Rbac;

/**
* 
*/
class TryController extends Controller {
	

	public function index() {
		// $this->assign('pageSize', $this->pageSize);
		$this->display();
	}


	public function setMenu(){
		die();
		$nav = C('MENU');
		
		foreach ($nav as $key => $value) {
			$data = [];
			$data['pid']   = 0;
			$data['icon']  = $value['icon'];
			$data['title'] = $value['title'];
			$data['href']  = "";
			$data['sort']  = 0;
			$id = M('menu_basic')->add($data);
			if ($id && isset($value['children'])) {
				foreach ($value['children'] as $index => $child ) {
					$child['pid'] = $id;
					$child['sort'] = $index;
					M('menu_basic')->add($child);
				}
			}
		}	
	}
}