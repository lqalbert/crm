<?php
namespace Home\Controller;

class NodeController extends CommonController{

	protected $table = "rbac_node";

	public function index(){
		$list = M($this->table)->where(array('level'=>array('gt','1')))->select();
		$this->assign("dataList", $this->reSort($list));
		$this->display();
	}

	private function reSort($list){
		$arr = array(); //新的数组
		foreach ($list as $value) {
			if ($value['pid'] == 1) {
				$arr[] = $value;
				$children = $this->findValue($list, $value['id']);
				foreach ($children as $child) {
					$arr[] = $child;
				}
			}
		}
		return $arr;
	}

	//找到指定的数组
	private function findValue($list, $pid){
		$arr = array();
		foreach ($list as $value) {
			if ($value['pid'] == $pid ) {
				$arr[]= $value;
			}
		}
		return $arr;
	}


		
}