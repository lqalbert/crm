<?php
namespace Home\Controller;
use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\CustomerModel;
use Home\Model\RealInfoModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
class GeneralServiceController extends CommonController{
	public function index(){
		$groupMemberList = M('user_info')->getField("user_id,realname");
    $this->assign('memberList',   $groupMemberList);
		$this->assign('customerType', D('Customer')->getType());
		$this->assign('sexType',      D('Customer')->getSexType());
		$this->assign('GoodsType',    D('CustomerLog')->getGoodsType());
		$this->display();
	}
}
