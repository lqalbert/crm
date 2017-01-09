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
		if (IS_POST) {
			$userModel = D('rbac_user');
			$where = array(
					'account' => I('post.account'),
					'password' => md5(I('post.password'))
				);

			$result = $userModel->relation('userInfo')->where($where)->find();
			if (!$result) {
				$this->error(L('LOGIN_ERROR'));
			} else {
				session('account', $result);
				session('uid', $result['id']);

				if ($result['no_authorized'] == CRM_SUPER_ADMIN) {
					// session(C['ADMIN_AUTH_KEY'], true);
					$_SESSION[C('ADMIN_AUTH_KEY')] = true;
				}
				//将权限写入session
				Rbac::saveAccessList();

				//$this->success(L('LOGIN_SUCCESS'), U('Index/index'));
				$this->redirect('Index/index');



			}
		}
	}


	public function logOut() {

		if(session("account")){
			session("uid",null);
			session("account",null);
			session('[destroy]');
		}
		$this->redirect("Login/index");
	}
}