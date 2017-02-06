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
          '1'=>"普通",
          '2'=>"优质",
 
      );

    protected $year = array(
          '1'=>"1年",
          '2'=>"2年",
          '3'=>"3年",
          '4'=>"4年",
          '5'=>"5年",
          '6'=>"6年",
          '7'=>"7年",
          '8'=>"8年",
          '9'=>"9年",
          '10'=>"10年",
          '11'=>"10年以上",

      );

    protected $income = array(
           '1'=>"大亏",
           '2'=>"小亏",
           '3'=>"持平",
           '4'=>"小赚",
           '5'=>"大赚",
      );

    protected $style = array(
          '1'=>"短线",
          '2'=>"中线",
          '3'=>"长线",
      );

    protected $money = array(
         '1'=>"5W以下",
         '2'=>"5W-10W",
         '3'=>"10W-20W",
         '4'=>"20W-50W",
         '5'=>"50W以上",
      );

    protected $energy = array(
         '1'=>"经常看盘",
         '2'=>"偶尔看盘",
         '3'=>"不怎么看盘",
      );

    protected $problem = array(
         '1'=>"不会判断大盘",
         '2'=>"不会选股",
         '3'=>"买卖点把握不好",
         '4'=>"仓位控制不好",
      );

    protected $mode = array(
          '1'=>"无",
          '2'=>"有，但不理想",
          '3'=>"有",
      );

    protected $attitude = array(
          '1'=>"友好",
          '2'=>"正常",
          '3'=>"恶劣",
      );

    protected $profession = array(
         '1'=>"退休人员",
         '2'=>"公务员",
         '3'=>"医生",
         '4'=>"教师",
         '5'=>"个体老板",
         '6'=>"学生",
         '7'=>"职业股民",
         '8'=>"公司职员",
         '9'=>"警察",
         '10'=>"国企职工",
      );

    protected $intention = array(
         '1'=>"主动了解",
         '2'=>"愿意了解",
         '3'=>"不排斥",
         '4'=>"排斥",
      );

    protected $source = array(
         '1'=>"东方财富",
         '2'=>"今日头条",
         '3'=>"新浪财经",
         '4'=>"企鹅 QQ公众平台",
         '5'=>"一点资讯",
         '6'=>"搜狐",
         '7'=>"微博",
         '8'=>"百度",
         '9'=>"同花顺",
         '10'=>"和讯",
         '11'=>"其他",
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