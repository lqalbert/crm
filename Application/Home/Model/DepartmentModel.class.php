<?php
namespace Home\Model;
use Think\Model;

class DepartmentModel extends Model {
	protected $tableName = 'department_basic';

	


	private $types = array(
		'我是总经办你想找事？',
		'区域',
		'事业部',
		'推广部',
	);


	/**
    * 返回类型 或 所有的类型
    * 
    * @return string|array
    */
    public function getType($index=null){
    	if (is_int($index)) {
    		return $this->types[$index];
    	} else {
    		return $this->types;
    	}
    }

}