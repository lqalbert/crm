<?php
namespace Home\Model;
use Think\Model;

class CustomerLogModel extends Model {

	protected $tableName = 'customers_log';


	protected $_auto = array(
		array('created_at','getDate', 1, 'callback')
		);

	private $types = array(
		"上门服务",
		"QQ联系",
		"Email联系",
		"微信联系",
		"其它方式"
	);


	/**
    * 返回类型 或 所有的类型
    * 
    * @return string|array
    */
    public function getType($index){
    	if (is_int($index)) {
    		return $this->types[$index];
    	} else {
    		return $this->types;
    	}
    }

    public function getDate(){
    	return Date('Y-m-d H:i:s');
    }
}