<?php
namespace Home\Controller;
use Think\Controller;
use Common\Lib\User;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
class RiskCtrlOneController extends CommonController{
	protected $table = "customers_service";
	protected $pageSize = 11;

  private function getOffset(){
      return (I('get.p',1)-1) * $this->pageSize;
  }

	public function index(){
		$groupMemberList = M('user_info')->getField("user_id,realname");
    $this->assign('memberList',   $groupMemberList);
		$this->assign('customerType', D('Customer')->getType());
		$this->assign('sexType',      D('Customer')->getSexType());
		$this->display();
	}

  public function getList(){
	  $this->setQeuryCondition();
	  $count=(int)$this->M->where(array('risk_one'=>'1'))->count();
	  $cusArr=$this->M->where(array('risk_one'=>'1'))->getField('cus_id', true);
	  $cusList=implode(",", $cusArr);
	  $list = M()->query("select * from customers_basic as cb INNER JOIN customers_contacts as cc on cb.id = cc.cus_id and cc.is_main = 1 
	  	WHERE cb.id IN (". $cusList .") ORDER BY cb.id desc LIMIT ".$this->getOffset().','.$this->pageSize);
	  $result = array('list'=>$list, 'count'=>$count);
    $this->ajaxReturn($result);
  }

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
       
    M('customers_basic')->join(' customers_contacts as cc on customers_basic.id =  cc.cus_id  and cc.is_main = 1');

	}























}
