<?php
namespace Home\Controller;

class IndexController extends CommonController {

	protected $pageSize = 4;


	public function index() {
		$this->display ();
	}

	public function main() {
		$this->assign("pageSize", $this->pageSize);
		$this->assign('NoticeType', D('sys_notice')->getType());
		$this->display();
	}


	public function getList(){
	    $count = (int)M('sys_notice')->count();
	    $list = M('sys_notice')->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
	    $result = array('list'=>$list, 'count'=>$count);
		$this->ajaxReturn($result);
	}



	//屏幕解锁
	public function lock(){
         echo '1';
	}
	//检查锁状态
	public function checkLock(){		
		$id=session('uid');
		$re=M('rbac_user')->where(array('id'=>$id,'lockpwd'=>md5(I('post.lockpwd'))))->find();
		if($re){
          echo 1;
		}else{
          echo 0;
		}
	}

	//培训学院 跳转地址
	public function up(){
		redirect('http://up.riign.cn/');
	}

	//素材库地址
	public function material(){
		redirect('http://www.riign.com/');
	}
}