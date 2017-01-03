<?php
namespace Home\Controller;

/**
* 左侧的菜单管理
*
*/

class MenuController extends CommonController {

	protected $table = 'menu';


	public function index() {

		$this->setQeuryCondition();
		$this->assign('pageSize', $this->M->count());
		$this->assign('nodeList', M('rbac_node')->field('id,title')->where(array('level'=>2))->select());
		$this->display();
	}

	/**
	 *  获取列表
	 *
	 * @return array() || null
	 * 
	 **/
	public function getList(){
		
		$this->setQeuryCondition();
		$count = (int)$this->M->count();
		$this->setQeuryCondition();
		$list = $this->M->select();
		$result = array('list'=>$this->M->reSort($list), 'count'=>$count);
		if (IS_AJAX) {
			$this->ajaxReturn($result);
		}  else {
			return $result;
		}

	}
}