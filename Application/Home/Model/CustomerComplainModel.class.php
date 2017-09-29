<?php
namespace Home\Model;

use Think\Model;

class CustomerComplainModel extends Model{
    protected $tableName = 'customers_complain';

    protected $_auto = array(
        array('user_id', 'getUser', 1, 'callback'),
    );


    private $types = array(
        "一般投诉",
        "工单投诉",
        "外诉倾向",
        "其他投诉"
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

    public function getUser(){
        return session('uid');
    }
}