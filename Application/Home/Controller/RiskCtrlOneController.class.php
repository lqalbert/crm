<?php
namespace Home\Controller;
use Think\Controller;
use Common\Lib\User;
class RiskCtrlOneController extends CommonController{
	public function index(){
		$groupMemberList = M('user_info')->getField("user_id,realname");
    $this->assign('memberList',   $groupMemberList);
		$this->assign('customerType', D('Customer')->getType());
		$this->assign('sexType',      D('Customer')->getSexType());
		$this->display();
	}
}
