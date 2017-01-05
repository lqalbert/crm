<?php
namespace Home\Controller;

class UploadController extends CommonController {

	public function index(){
		$folder=I('post.folder');
		$upload = new \Think\Upload();// 实例化上传类
	    $upload->maxSize   =     3145728 ;// 设置附件上传大小3M
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $upload->rootPath  =     './Upload/'.$folder.'/'; // 设置附件上传根目录
	    $upload->autoSub   =     false;//不自动创建子目录
	    // 上传文件 
	    $info   =   $upload->upload();
	    if(!$info) {// 上传错误提示错误信息
	        $this->error($upload->getError());
	    }else{// 上传成功
	        $this->ajaxReturn(array('path'=>$info['file']['savename']));
	    }
	}
}