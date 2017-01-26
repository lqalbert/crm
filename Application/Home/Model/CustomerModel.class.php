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
	const TYPE_V = 7;

	const UNKNOW = 0;
	const MAN    = 1;
	const WOMAN  = 2;


    protected $tableName = 'customers_basic';
    protected $customerType = array(
    	    "A"=>"A.准客户",
          "B"=>"B.意向客户",
          "C"=>"C.一般客户",
          "D"=>"D.未有意向客户",
          // "E.本地化客户",
          "F"=>"F.黑名单（同行）",
          "N"=>"N.无效客户",
          "V"=>"V.成交客户",
    	);

    protected $sexType = array(
    	  "未选择",
          "男",
          "女"
    	);

    protected $quality = array(
          "普通",
          "优质",
 
      );

    protected $year = array(
          "1年",
          "2年",
          "3年",
          "4年",
          "5年",
          "6年",
          "7年",
          "8年",
          "9年",
          "10年",
          "10年以上",

      );

    protected $income = array(
           "大亏",
           "小亏",
           "持平",
           "小赚",
           "大赚",
      );

    protected $style = array(
          "短线",
          "中线",
          "长线",
      );

    protected $money = array(
         "5W以下",
         "5W-10W",
         "10W-20W",
         "20W-50W",
         "50W以上",
      );

    protected $energy = array(
         "经常看盘",
         "偶尔看盘",
         "不怎么看盘",
      );

    protected $problem = array(
         "不会判断大盘",
         "不会选股",
         "买卖点把握不好",
         "仓位控制不好",
      );

    protected $mode = array(
          "无",
          "有，但不理想",
          "有",
      );

    protected $attitude = array(
          "友好",
          "正常",
          "恶劣",
      );

    protected $profession = array(
         "退休人员",
         "公务员",
         "医生",
         "教师",
         "个体老板",
         "学生",
         "职业股民",
         "公司职员",
         "警察",
         "国企职工",
      );

    protected $intention = array(
         "主动了解",
         "愿意了解",
         "不排斥",
         "排斥",
      );

    protected $source = array(
         "东方财富",
         "今日头条",
         "新浪财经",
         "企鹅 QQ公众平台",
         "一点资讯",
         "搜狐",
         "微博",
         "百度",
         "同花顺",
         "和讯",
         "其他",
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
    public function getType($index=NULL){
    	if (!empty($index)) {
    		return $this->customerType[strtoupper($index)];
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

    /**
    * 返回客户质量的汉字
    * @return string|array
    */
    public function getQuality($index){
      if (is_int($index)) {
        return $this->quality[$index];
      } else {
        return $this->quality;
      }
    }

    /**
    * 返回客户股龄的汉字
    * @return string|array
    */
    public function getYear($index){
      if (is_int($index)) {
        return $this->year[$index];
      } else {
        return $this->year;
      }
    }

    /**
    * 返回客户近期收益的汉字
    * @return string|array
    */
    public function getIncome($index){
      if (is_int($index)) {
        return $this->income[$index];
      } else {
        return $this->income;
      }
    }

    /**
    * 返回客户投资风格的汉字
    * @return string|array
    */
    public function getStyle($index){
      if (is_int($index)) {
        return $this->style[$index];
      } else {
        return $this->style;
      }
    }
    
    /**
    * 返回客户资金量的汉字
    * @return string|array
    */
    public function getMoney($index){
      if (is_int($index)) {
        return $this->money[$index];
      } else {
        return $this->money;
      }
    }

    /**
    * 返回客户看盘精力的汉字
    * @return string|array
    */
    public function getEnergy($index){
      if (is_int($index)) {
        return $this->energy[$index];
      } else {
        return $this->energy;
      }
    }

    /**
    * 返回客户投资问题的汉字
    * @return string|array
    */
    public function getProblem($index){
      if (is_int($index)) {
        return $this->problem[$index];
      } else {
        return $this->problem;
      }
    }

    /**
    * 返回客户盈利模式的汉字
    * @return string|array
    */
    public function getMode($index){
      if (is_int($index)) {
        return $this->mode[$index];
      } else {
        return $this->mode;
      }
    }

    /**
    * 返回客户态度的汉字
    * @return string|array
    */
    public function getAttitude($index){
      if (is_int($index)) {
        return $this->attitude[$index];
      } else {
        return $this->attitude;
      }
    }

    /**
    * 返回客户职业的汉字
    * @return string|array
    */
    public function getProfession($index){
      if (is_int($index)) {
        return $this->profession[$index];
      } else {
        return $this->profession;
      }
    }

     /**
    * 返回客户软件意向的汉字
    * @return string|array
    */
    public function getIntention($index){
      if (is_int($index)) {
        return $this->intention[$index];
      } else {
        return $this->intention;
      }
    }
    
    /**
    * 返回客户来源的汉字
    * @return string|array
    */
    public function getSource($index){
      if (is_int($index)) {
        return $this->source[$index];
      } else {
        return $this->source;
      }
    }         
}