<?php
namespace Home\Controller;

class UploadController extends CommonController {

	private $upload = null;

	private function upload($authSub = false) {
		$folder=I('post.folder');
		$this->upload = new \Think\Upload();// 实例化上传类
	    $this->upload->maxSize   =     3145728 ;// 设置附件上传大小3M
	    $this->upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $this->upload->rootPath  =     './Upload/'.$folder.'/'; // 设置附件上传根目录
	    $this->upload->autoSub   =     $authSub;
	    return $this->upload->upload();
	}

	public function index(){
	    $info   =   $this->upload();
	    if(!$info) {// 上传错误提示错误信息
	        $this->error($this->upload->getError());
	    }else{// 上传成功
	        $this->ajaxReturn(array('path'=>$info['file']['savename']));
	    }
	}

	public function index2(){
		
	    $info   =   $this->upload(true);
	    if(!$info) {// 上传错误提示错误信息
	        $this->error($this->upload->getError());
	    }else{// 上传成功
	        $this->ajaxReturn(array('path'=>substr($this->upload->rootPath, 1 ).$info['file']['savepath'].$info['file']['savename']));
	    }
	}
}