<?php
namespace Home\Controller;
use Think\Controller;
use Think\Verify;
class LoginController extends Controller {
   //后台登录页面
		function Login(){
			 $this->display();
		}

	 //后台登陆验证
	 function check(){
		   $_SESSION['admin']="chengdu_riign";
		   $this->redirect("Index/index");
  }

}