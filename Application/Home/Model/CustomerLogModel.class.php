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
        "上门服务",
        //"网络跟踪",
        "QQ联系",
        "Email联系",
        "微信联系",
        "远程讲解",
        "现场讲解",
        "成交总结",
        "讲师指导",
        "主管建议",
        "售前回访",
		//"Email联系",
		"其它方式"
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
    
    //服务比例
    private $proportion = array(
      '0'=>'0',
      '1'=>'10',
      '2'=>'20',
      '3'=>'30',
      '4'=>'40',
      '5'=>'50',
      '6'=>'60',
      '7'=>'70',
      '8'=>'80',
      '9'=>'90',
    );
    
    //提醒事项
    private $remind = array(
      '1'=>'客户重复加群，麻烦尽快跟踪',
      '2'=>'对软件有意向',
      '3'=>'客户之前了解过公司',
      '4'=>'客户排斥软件',
      '5'=>'客户排斥电话沟通',
      '6'=>'客户加同事好友不通过',
      '7'=>'白天要上班不能接电话',
      '8'=>'资金量大，重点跟踪',
      '9'=>'二次推荐，已再次跟踪包装',
      '10'=>'请跟踪方同事反馈情况',
      '11'=>'客户一直在咨询股票，请联系远程',
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
      return UTC_to_locale_time($v);
    }

    public function getUser(){
        return session('uid');
    }
    
    //获得跟踪比例
    public function getProportion($index){
        if(is_int($index)){
            return $this->proportion[$index];
        }else{
            return $this->proportion;
        }
    }

    //获得提醒事项
    public function getRemind($index){
       if(is_int($index)){
          return $this->remind[$index];
       }else{
          return $this->remind;
       }
    }

    public function contentSetChangeType($from, $to){
        $C = D('Customer');
        $this->content = $C ->getType($from)."=>".$C->getType($to)."\r\n". $this->content;
    }
}