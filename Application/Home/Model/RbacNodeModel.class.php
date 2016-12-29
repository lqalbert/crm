<?php
namespace Home\Model;
use Think\Model;
class RbacNodeModel extends Model {
    protected $tableName = 'rbac_node';




    public function delete($ids) {
    	$where    = array("pid"=>array('in', $ids), 'level'=>array('eq', 3));
    	$nodeList = $this->where($where)->getField('id', true);

    	if ($nodeList) {
    		$result = parent::delete(implode(',', $nodeList));
    		if ($result !== false ) {
    			return parent::delete($ids);
    		} else {
    			return false;
    		}
    	} else {
    		return parent::delete($ids);
    	}
    }

    public function reSort($list){
		$arr = array(); //新的数组
		if (!is_array($list)) {
			$list = $this->data();
		}
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
	public function findValue($list, $pid){
		$arr = array();
		foreach ($list as $value) {
			if ($value['pid'] == $pid ) {
				$arr[]= $value;
			}
		}
		return $arr;
	} 

	/**
	* 过滤 level 1
	*/
	public function setFilterLevelOne(){
		$this->where(array('level'=>array('gt', '1')));
		return $this;
	}


	
}