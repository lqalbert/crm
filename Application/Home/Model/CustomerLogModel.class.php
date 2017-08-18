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
        //"远程讲解",
        // "现场讲解",
        "成交总结",
        "讲师指导",
        "主管建议",
        "售前回访",
        "风控建议",
        "经理建议",
        "反馈投诉",
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
    
    //跟踪比例分配
    private $proportion = array(
      '1'=>'跟90%锁10%',
      '2'=>'跟80%锁20%',
      '3'=>'跟70%锁30%',
      '4'=>'跟60%锁40%',
      '5'=>'跟50%锁50%',
      '6'=>'跟40%锁60%',
      '7'=>'跟30%锁70%',
      '8'=>'跟20%锁80%',
      '9'=>'跟10%锁90%',
      '10'=>'跟100%锁0%',
    );
    
    //锁定方比例
    private $addProportion = array(
      '1'=>'10',
      '2'=>'20',
      '3'=>'30',
      '4'=>'40',
      '5'=>'50',
      '6'=>'60',
      '7'=>'70',
      '8'=>'80',
      '9'=>'90',
      '10'=>'0',
    );

    //跟踪方比例
    private $trackProportion = array(
      '1'=>'90',
      '2'=>'80',
      '3'=>'70',
      '4'=>'60',
      '5'=>'50',
      '6'=>'40',
      '7'=>'30',
      '8'=>'20',
      '9'=>'10',
      '10'=>'100',
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
 
    //产品类型
    private $goodsType = array(
       '1'=>'点金手高端版(体验版)',
       '2'=>'点金手高端版(季度)',
       '3'=>'点金手高端版(半年)',
       '4'=>'点金手高端版(一年)',
       '5'=>'点金手黄金版(半年)',
       '6'=>'点金手黄金版(一年)',
       '7'=>'点金手至尊版(一年)',
       '8'=>'点金手至尊版(两年)',
       '9'=>'企业投资理财大全(中小企业板)',
       '10'=>'企业投资理财大全(小微企业版)',
    );
    
    //服务周期
    private $serviceCycle = array(
      '1'=>'1个月',
      '2'=>'2个月',
      '3'=>'1季度',
      '4'=>'半年',
      '5'=>'9个月',
      '6'=>'1年',
      '7'=>'1年半',
      '8'=>'两年',
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

    public function getSteps($index){
      if (is_int($index)){
        return $this->steps[$index];
      }else{
        return $this->steps;
      }
        
    }


    public function getDate(){
    	return Date('Y-m-d H:i:s');
    }

    public function transfer($v){
        if ($v) {
          return UTC_to_locale_time($v);
        } else {
          return null;
        }
        
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

    //获得锁定方比例
    public function getAddProportion($index){
        if(is_int($index)){
            return $this->addProportion[$index];
        }else{
            return $this->addProportion;
        }
    }

    //获得跟踪方比例
    public function getTrackProportion($index){
     
        if(is_int($index)){
            return $this->trackProportion[$index];
        }else{
            return $this->trackProportion;
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
    
    //获得产品类型
    public function getGoodsType($index){
       if(is_int($index)){
          return $this->goodsType[$index];
       }else{
          return $this->goodsType;
       }
    }

    //获得服务周期
    public function getServiceCycle($index){
       if(is_int($index)){
          return $this->serviceCycle[$index];
       }else{
          return $this->serviceCycle;
       }
    }
    public function contentSetChangeType($from, $to){
        $C = D('Customer');
        $this->content = $C ->getType($from)."=>".$C->getType($to)."\r\n". $this->content;
    }
}