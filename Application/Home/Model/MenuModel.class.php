<?php
namespace Home\Model;

use Think\Model;
use Common\Lib\Arr;

class MenuModel extends Model {
	protected $tableName = 'menu_basic';


	public function delete($ids) {
    	$where    = array("pid"=>array('in', $ids));
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
		return Arr::reSort($list, 'pid', 0);
	}
}