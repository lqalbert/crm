<?php
namespace Home\Controller;

use Think\Controller;
use Org\Util\Rbac;

/**
* 
*/
class LoginController extends Controller {
	
	public function index () {
		$this->display();
	}

	public function loginHandle() {
		//var_dump($_SESSION);die();
		if (IS_POST) {
			$userModel = D('rbac_user');
			$where = array(
					'account' => I('post.account'),
					'password' => md5(I('post.password'))
				);
			$result = $userModel->relation('userInfo')->where($where)->find();
			if (!$result) {
        		$this->ajaxReturn(array('msg'=>L('LOGIN_ERROR'),'code'=>-1),'JSON');
			} else {
        		$sessionId=session_id();
        		session('ssid', $sessionId);
       			// $userModel->where(array('id'=>$result['id']))->save(array('ss_id'=>$sessionId));
        
				// $location=new \Ip\Taobaoip\taobaoIp();//利用淘宝地址库
				// $arr=$location->getLocation();
				$data=array(
                   //'ip'=>$arr['ip'],
                   // 'location'=>$arr['country'].$arr['region'].$arr['city'].$arr['county'],
                   'lg_time'=>time(),
                   'ss_id'  =>$sessionId
				);
				$re=$userModel->where(array('id'=>$result['id']))->save($data);
				$_SESSION['location']= $re===false ? '' : $data;
				session('account', $result);
				session('uid', $result['id']);

				if ($result['no_authorized'] == CRM_SUPER_ADMIN) {
					// session(C['ADMIN_AUTH_KEY'], true);
					$_SESSION[C('ADMIN_AUTH_KEY')] = true;
				}
				//将权限写入session
				Rbac::saveAccessList();
				//$this->redirect('Index/index');
				$this->ajaxReturn(array('msg'=>L('LOGIN_SUCCESS'),'code'=>1),'JSON');

			}
		} else {
			$this->redirect('index');
		}
	}


	public function logOut() {
  	$user_id=session('uid');
  	M('rbac_user')->where(array('id'=>$user_id))->save(array('out_time'=>time()));
		if(session("account")){
			session("uid",null);
			session("account",null);
			session(null);
			session('[destroy]');
		}
		session('[destroy]');
		$this->redirect("Login/index");
	}

}



