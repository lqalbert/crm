<?php
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\CustomerModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
use Home\Model\ProductModel;
use Think\Model;
class AllUserCustomerTreeController extends CommonController{
	private $depart_id = 0;
	protected $table="Customer";
	protected $pageSize = 11;

	public function _initialize(){
	    parent::_initialize();
	    $this->depart_id = $this->getDepartMentID();
	}

	private function getDepartMentID(){
	    $re =  M('department_basic')->where(array('user_id'=>session('uid')))->getField('id');
	    if (is_numeric($re)) {
	        return $re;
	    } else {
	        return 0;
	    }
	}



  public function index(){
    $D = D('Customer');
    $id = I('get.id', '');
    $department_id = I('get.department_id', '');
    $group_id = I('get.group_id', '');
    $this->assign('user_id', $id);
    $this->assign('department_id', $department_id);
    $this->assign('group_id', $group_id);
    $this->assign('customerType', $D->getType());
    $this->assign('sexType',      $D->getSexType());
    $this->assign('Departments',  D('Department')->getAllDepartments('id,name'));
    $this->display();
  }


  private function checkLikeField(){
      //改造成复合查询
      if (I('get.name')) {
          $this->M->where(array('name'=>array('like', I('get.name')."%")));
      }

      $complexWhere = array('_logic'=>'OR');
      $arrList = array('phone', 'qq', 'weixin', );
      foreach ($arrList as $value) {
          if (I('get.'.$value)) {
              $complexWhere['cc.'.$value] = array('like', I('get.'.$value)."%");
              $complexWhere['cc2.'.$value] = array('like', I('get.'.$value)."%");
          }
      }

      if (count($complexWhere)>1) {
          $this->M->where(array('_complex'=>$complexWhere));
      }
  }


  /**
  * 设置查询参数
  * 
  * @return null
  */
  public function setQeuryCondition() {
      $this->M->setShowCondition();
      //手机 QQ WEIXIN name
      $this->checkLikeField();
   
      //个人
      if (I('get.user_id')) {
          $this->M->setSalesman(I('get.user_id')); //$this->where(array('salesman_id'=>$value));
      } else if(I('get.department_id')){
          $this->M->join('left join user_info as ufo on ufo.user_id=customers_basic.salesman_id')
          ->where(array('ufo.department_id'=>I('get.department_id')));
      }else if(I('get.group_id')){
          $this->M->join('left join user_info as ufo on ufo.user_id=customers_basic.salesman_id')
          ->where(array('ufo.group_id'=>I('get.group_id')));
      }


      // 如果一个时间都没传
      // 近3个月之内的
      if ( empty(I('get.start')) 
           && empty(I('get.end')) 
           && empty(I('get.track_start')) 
           && empty(I('get.track_end')) 
           && strpos(I('get.field'),'transf') === false
           ) {
         // $this->M->setStart('created_at', D('Customer','Logic')->ThreeMonthsAge());
      }

      $this->M->join(' customers_contacts as cc on customers_basic.id =  cc.cus_id  and cc.is_main = 1')
              ->join('left join customers_contacts as cc2 on customers_basic.id =  cc2.cus_id and cc2.is_main = 0');


     
      
  }



  public function _getList(){
      $this->setQeuryCondition();
      //没有 is_main
      $count = (int)$this->M->count();
      
      $this->setQeuryCondition();
      D('Customer','Logic')->getJoinCondition($this->M);
      if (I('get.sort_field', null)) {
          $this->M->order(I('get.sort_field')." ". I('get.sort_order'));
      } else {
          $this->M->order('customers_basic.id desc');
      }
      $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();
      //echo M()->getLastSql();die();
      $result = array('list'=>$list, 'count'=>$count);
      return $result;
  }


  /**
  *   获取跟踪信息
  *
  */
public function trackInfo(){
      $arr=D('Customer','Logic')->trackInfo($this->M);
	if (IS_AJAX) {
		$this->ajaxReturn($arr);
	}  else {
		return $arr;
	}
}


  /**
  *  添加客户真实资料
  *  
  */
  public function realInfo(){ 
      $ob=D('RealInfo');
      $cus_id = I('post.cus_id');
      $_POST['user_id']= D('Customer')->where(array('id'=>$cus_id))->getField('salesman_id');//session('uid'); 
      if($ob->where(array('cus_id'=>I('post.cus_id'),'identity'=>I('post.identity')))->find()){
          $ob->where(array('cus_id'=>I('post.cus_id')))->save(I('post.'));
          M('customers_service')->where(array('cus_id'=>I('post.cus_id'),'user_id'=>I('post.user_id')))->setField('call_back','1');
      }else{
          if($ob->create($_POST) && $ob->add()){
              $data=array(
                  'cus_id'=>I('post.cus_id'),
                  'user_id'=>I('post.user_id'),
                  'risk_one'=>'1'     
              );
              M('customers_service')->add($data);
              $this->success(L('真实资料添加成功'));
          }else{
              $this->error($ob->getError());    
              
          }
      }
  }




















}