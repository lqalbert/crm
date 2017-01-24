<?php
namespace Home\Model;
use Think\Model;

class CustomerLogModel extends Model {

	protected $tableName = 'customers_log';


	protected $_auto = array(
		array('created_at','getDate', 1, 'callback'),
        array('next_datetime', 'transfer', 1, 'callback'),
        array('user_id', 'getUser', 1, 'callback'),
		);

	private $types = array(
        "电话跟踪",
        "网络跟踪",
        "远程讲解",
        "现场讲解",
        "成交总结",
        "讲师指导",
        "主管建议",
        "售前回访",

		/*"上门服务",
		"QQ联系",
		"Email联系",
		"微信联系",
		"其它方式"*/
	);
    private $steps = array(
        "首次沟通",
        "早盘",
        "基本信息",
        "沟通介入",
        "远程讲解",
        // "现场邀约",
        // "来过现场",
        "提升",
        "洽谈",
        "其它",
        "售前确认"

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

    public function getSteps(){
        return $this->steps;
    }


    public function getDate(){
    	return Date('Y-m-d H:i:s');
    }

    public function transfer($v){
        return Date('Y-m-d H:i:s', strtotime($v));
    }

    public function getUser(){
        return session('uid');
    }

    public function contentSetChangeType($from, $to){
        $C = D('Customer');
        $this->content = $C ->getType($from)."=>".$C->getType($to)."\r\n". $this->content;
    }
}