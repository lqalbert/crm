<?php
namespace Home\Controller;

use Think\Model;

/**
*
* todo 建立模型 数据验证
*/
class RoleController extends CommonController{
	protected $table = "rbac_role";


	public function index(){
		
		$this->assign('pageSize', $this->M->count());
		$this->assign('nodeList', $this->getNodes());
		$this->display();
	}

	public function _before_getList(){
		$this->pageSize = $this->M->count();
	}

	/**
	* 获取节点
	* @return array()
	*/
	public function getNodes(){
		$nodeM = D('rbac_node');
		$nodeList = $nodeM->order("sort asc")->select();
		// var_dump(arr_to_map($nodeList, 'id'));
		$nodeList = arr_to_tree(arr_to_map($nodeList, 'id'), 'pid');
		return array_values(array_values($nodeList)[0]['sons']) ;
	}


	/**
	* 授权
	*/
	public function setAccess(){
		$M = D('rbac_access');
		$insert_list = array();
		
		$role_id = I('post.role_id',0);
		$level1_node_ids = I('post.node');
		$level1_nodes = arr_to_map($this->getNodesInfo($level1_node_ids), 'id');

		$level2_node_ids = I('post.cnode');
		$level2_nodes = arr_to_map($this->getNodesInfo($level2_node_ids), 'id');
		foreach ($level2_nodes as $key => $value) {
			if (!isset($level1_node[$value['pid']])) {
				$level1_nodes[$value['pid']] = D('rbac_node')->find($value['pid']);
			}
		}
		// 第一个为顶级模块 写死了 无法编辑 如果更改会出错
		$insert_list[] = array('role_id'=> $role_id, 'node_id'=> 1, 'level'=>1, 'module'=>'Home');
		foreach (array_merge($level1_nodes, $level2_nodes) as $value) {
			$insert_list[] = array(
				'role_id' => $role_id, 
				'node_id' => $value['id'], 
				'level'   => $value['level'], 
				'module'  => $value['name']);
		}

		$M->startTrans(); 
		$result = $M->where(array('role_id'=>$role_id))->delete();
		if ($result !== false) {
			$insert_result = $M->addAll($insert_list);
			if ($insert_result !== false) {
				$M->commit();
				$this->success("操作成功");
			} else {
				$M->rollback();
				$this->error("操作失败");
			}
		} else {
			$M->rollback();
			$this->error("操作失败");
		}
	}

	/**
	* 获取 授权的列表
	* @param role_id
	* @return array()
	*/
	public function getAccess(){
		$M = D('rbac_access');
		$role_nodes =arr_group($M->where(array('role_id'=> I('get.role_id',0), 'level'=>array('gt','1')))->select(), 'level');
		ksort($role_nodes);
		// dump($role_nodes);
		$this->ajaxReturn(array_values($role_nodes));
	}



	/**
	* 获取node 节点信息
	* 
	* @param array()
	*
	* @return array()
	*/
	public function getNodesInfo($ids){
		
		return D('rbac_node')->where(array("id"=>array('in', $ids)))->select();
	}
}





































?>