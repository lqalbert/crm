<?php
namespace Home\Controller;

class IndexController extends CommonController {
	public function index() {
		$this->display ();
	}

	public function main() {
		$this->assign("pageSize", 0);
		$this->display ();
	}

	
	public function getList(){
		$this->ajaxReturn(array());
	}

	//屏幕解锁
	public function lock(){
         echo '1';
	}
	//
	public function checkLock(){		
		$id=session('uid');
		$re=M('rbac_user')->where(array('id'=>$id,'lockpwd'=>md5(I('post.lockpwd'))))->find();
		if($re){
          echo 1;
		}else{
          echo 0;
		}
	}
}