<?php
namespace Home\Controller;

class NodeController extends CommonController{

	protected $table = "rbac_node";

	public function index(){
		
		$this->setQeuryCondition();
		// $this->pageSize = $this->M->count();
		$this->assign('pageSize', $this->M->count());

		$this->display();
	}

	/**
	 * 公用 设置参数
	 * 子类
	 * @return  null
	 * 
	 **/
	public function setQeuryCondition() {

		$this->M->where(array('level'=>array('gt', '1')));
	}


	/**
	 * 公用 获取列表
	 *
	 * @return array() || null
	 * 
	 **/
	public function getList(){
		
		$this->setQeuryCondition();
		$count = (int)$this->M->count();
		$this->setQeuryCondition();
		$list = $this->M->select();
		$result = array('list'=>$this->reSort($list), 'count'=>$count);
		if (IS_AJAX) {
			$this->ajaxReturn($result);
		}  else {
			return $result;
		}

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