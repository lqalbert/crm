<?php
namespace Home\Controller;
use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\RealInfoModel;

class PerformanceController extends CommonController {
	
	protected $pageSize = 11;

	public function index () {
		// $dataList = $this->getList();
        // var_dump(D('Customer','Logic')->sb);die();
        $user = new User();
        $searchGroup = $user->getRoleObject()
                            ->getCustomerSearchGroup(array(array('value'=>'user_id','key'=>"本人" ) , array('value'=>'group','key'=>"团组" )));
        $groupMemberList = M('user_info')->where(array('group_id'=>session('account')['userInfo']['group_id']))->getField("user_id,realname");

        $this->assign('searchGroup',  $searchGroup);


	


		$this->display();

	}

    
   
}