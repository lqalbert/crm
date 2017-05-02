<?php
namespace Home\Model;
use Think\Model;
class CustomerModel extends Model {

	const TYPE_A = 'A';
	const TYPE_B = 'B';
	const TYPE_C = 'C';
	const TYPE_D = 'D';
	const TYPE_E = 'E';
	const TYPE_N = 'F';
  const TYPE_F = 'N';
  const TYPE_V = 'V';
  const TYPE_VX = 'VX';
	const TYPE_VT = 'VT';

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
          "VX"=>"VX.审核未通过",
          "VT"=>"VT.有意见并投诉",
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
    		array('name','require', '姓名必须！'), 

    );

    protected $_auto = array(
        array('plan', 'transfer', 1, 'callback'),
        array('last_track', 'getDate', 1, 'callback'),
    );
    //UTC时间转换
    public function transfer($v){
      if (empty($v)) {
        return null;
      } else {
        return UTC_to_locale_time($v);
      }
    }

    public function getDate(){
      return Date("Y-m-d H:i:s");
    }

    

    public function add(){
      //开启事务
      // die('asdf');
      $this->startTrans();
      $id = parent::add();
      if ($id == false) {
        $this->rollback();
        return false;
      }

      $D_contact = D('CustomerContact');
      $mainData = $D_contact->getMainPost();
      $d = $D_contact->create($mainData, self::MODEL_INSERT);
      if ($d == false) {
        $this->rollback();
        $this->error = $D_contact->getError();
        // $this->addConflict($mainData );

        return false;
      }

      $d['is_main'] = 1;
      $d['cus_id'] = $id;
      if ($D_contact->data($d)->add()) {

        //第二套手机 qq 和 微信
        $data = $D_contact->getSecondPost();
        if (!empty($data['phone']) || !empty($data['qq']) || !empty($data['weixin'])) {
          $data['cus_id'] = $id;
          if ( !($D_contact->create($data) && $D_contact->add())) {
            $this->rollback();
            $this->error = $D_contact->getError($data );
           // $this->addConflict($data);
            return false;
          }
        }

        $this->commit();
        return true;
      }else{
        $this->error = D('CustomerContact')->getError();
        $this->rollback();
        return false;
      }
    }

    /**
    * 废弃不用了
    */
    private function addConflict($data){
      /*$error = $this->error;
      if (mb_strpos($error, '存在')!==false) {

         if (mb_strpos($error, '手机')!==false) {
           $cus_id = $this->getConflictCusId('phone',$data);
           D('CustomerConflict')->addPhone($cus_id, $data['phone']);
         } else if(mb_strpos($error, 'QQ')!==false){

           $cus_id = $this->getConflictCusId('qq', $data);
           D('CustomerConflict')->addQQ($cus_id, $data['qq']);

         } else if(mb_strpos($error, '微信')!==false){
           $cus_id = $this->getConflictCusId('weixin', $data);
           D('CustomerConflict')->addWx($cus_id, $data['weixin']);
         }

      }*/
    }

    private function getConflictCusId($field, $data){
      return D('CustomerContact')->where(array($field=>$data[$field]))->getField('cus_id');
    }

    


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

    /**
    * 用于索取
    * 改变服务的员工即改变 salesman_id 
    * 重置service_time 为当前的时间戳
    * 添加一条被索取的纪录
    * 通知被索取的员工
    * （后面两点可以用异步来实现的）
    *
    * @param cus_id int 客户id
    * @param user_id int 索取的员工id
    *
    * @return boolean true false
    */
    public function changeSalesman($cus_id, $user_id) {
      $condition = array('id'=>$cus_id);
      $old_userid = $this->where($condition)->getField('salesman_id');

      if ($old_userid == $user_id) {
        $this->error = "不能索取你自己的客户";
        return false;
      }

      $this->startTrans();

      //索取
      $data = array(
        'salesman_id'=>$user_id,
        'service_time'=>time()
      );
      $re = $this->data($data)->where($condition)->save();
      if (!$re) {
        $this->rollback();
        $this->error = "索取失败";
        return false;
      }

      //添加记录 感觉下面的操作在这个事务以外也可以，似乎更合理
      // 赶时间 先这样了
      $logData = array(
          'from_id'=>$old_userid,
          'to_id'  =>$user_id,
          'cus_id' =>$cus_id
      );
      $re = M('customers_pulls')->data($logData)->add();

      if (!$re) {
        $this->rollback();
        $this->error = "索取日志添加失败";
        return false;
      }
      //通知被索取的员工
      $re = D('MsgBox')->add(array(
          'title'=>"索取通知",
          'content'=>'您有一名客户被 索取',
          'to_id'  => $old_userid,
          'from_id'=> $user_id
        ));
      if (!$re) {
        $this->rollback();
        $this->error = "索取通知发送失败";
        return false;
      }
      $this->commit();

      return true;
    }


    public function setStart($field, $value){
      $this->where(array($field=>array('EGT', $value." 00:00:00")));
    }

    public function setEnd($field, $value){
      $this->where(array($field=>array('ELT', $value." 23:59:59")));
    }  

    /**
    * salesman_id
    */
    public function setSalesman($value){
      $this->where(array('salesman_id'=>$value));
    }


    public function setShowCondition(){
      $this->where(array('customers_basic.status'=>1));
    }

    public function setTransf($id){
      return $this->data(array('transfer_status'=>1))->where(array('id'=>$id))->save();
    }

    public function delete($ids){
        return $this->where(array('id'=>array('in', $ids )))->save(array('status'=>-1));
    }

    public function setTimeDiv($field, $start=null, $end=null){
      if ($start && $end) {
         $this->where(
          array(
            $field=>array( array('EGT', $start." 00:00:00"), array('ELT', $end." 23:59:59"))));
      }else if($start){
        $this->setStart($field, $start);
      } else if($end){
        $this->setEnd($field, $end);
      }
    }


}