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
}