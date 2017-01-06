<?php
namespace Home\Model;
use Think\Model;
class CustomerModel extends Model {

	const TYPE_A = 0;
	const TYPE_B = 1;
	const TYPE_C = 2;
	const TYPE_D = 3;
	const TYPE_E = 4;
	const TYPE_N = 5;
	const TYPE_F = 6;

	const UNKNOW = 0;
	const MAN    = 1;
	const WOMAN  = 2;


    protected $tableName = 'customers_basic';
    protected $customerType = array(
    	    "A.准客户",
          "B.意向客户",
          "C.一般客户",
          "D.未有意向客户",
          "E.本地化客户",
          "N.无效客户",
          "F.黑名单（同行）",
    	);

    protected $sexType = array(
    	  "未选择",
          "男",
          "女"
    	);


    protected $_validate = array(
		array('name','require','姓名必须！'), //默认情况下用正则进行验证
		array('phone','',      '手机号已经存在！', self::MUST_VALIDATE, 'unique'), // 验证phone字段是否唯一
		array('qq','',         'QQ号已经存在！',   self::MUST_VALIDATE, 'unique'), // 验证qq字段是否唯一
		array('weixin','',     '微信号已经存在！', self::MUST_VALIDATE, 'unique'), // 验证微信号是否唯一
   );


    /**
    * 返回类型 或 所有的类型
    * 
    * @return string|array
    */
    public function getType($index){
    	if (is_int($index)) {
    		return $this->customerType[$index];
    	} else {
    		return $this->customerType;
    	}
    }

    /**
    * 返回性别的汉字
    * @return string|array
    */
    public function getSexType($index){
    	if (is_int($index)) {
    		return $this->sexType[$index];
    	} else {
    		return $this->sexType;
    	}
    }

}