<?php
namespace Home\Controller;

class UploadController extends CommonController {

	private $upload = null;

	private function upload($authSub = false) {
		$folder=I('post.folder');
		//$folder= $_POST['folder'];
		$this->upload = new \Think\Upload();// 实例化上传类
	    $this->upload->maxSize   =     3145728 ;// 设置附件上传大小3M
	    $this->upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $this->upload->rootPath  =     './Upload/'.$folder.'/'; // 设置附件上传根目录
	    $this->upload->autoSub   =     $authSub;
	    return $this->upload->upload();
	}

	public function index(){  //
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

  //对layui上传的图片单独处理
	public function index3($folder,$authSub=true){
		  $upload = new \Think\Upload();// 实例化上传类
	    $upload->maxSize   =     3145728 ;// 设置附件上传大小3M
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $upload->rootPath  =     './Upload/'.$folder.'/'; // 设置附件上传根目录
	    $upload->autoSub   =     $authSub;
	    $info   = $upload->upload();
	    if(!$info) {// 上传错误提示错误信息
				$this->ajaxReturn(array('yes'=>'上传失败，换个姿势再来一次！'),'JSON');
	    }else{// 上传成功
	    	$user_id=session('uid');
	    	foreach ($info as $file) {
	    		$data=array('head'=>substr($upload->rootPath, 1 ).$file['savepath'].$file['savename']);
	    	}
	    	$res=M('user_info')->where(array('user_id'=>$user_id))->save($data);
	    	if($res){
     			$_SESSION['account']['userInfo']['head']=$data['head'];
     			$this->ajaxReturn(array('yes'=>'上传成功，请刷新查看！'),'JSON');
	    	}else{
     			$this->ajaxReturn(array('yes'=>'上传失败，换个姿势再来一次！'),'JSON');
	    	}   
	    }
	}

  //处理微信和QQ二维码截图裁图缩图
  public function imageCropperUpload($folder,$authSub=true){
	  $upload = new \Think\Upload();// 实例化上传类
    $upload->maxSize   =     3145728 ;// 设置附件上传大小3M
    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    $upload->rootPath  =     './Upload/'.$folder.'/'; // 设置附件上传根目录
    $upload->autoSub   =     $authSub;
    $info   = $upload->upload();
    if(!$info) {// 上传错误提示错误信息
			echo $upload->getError();
    }else{// 上传成功
    	$user_id=session('uid');
    	foreach ($info as $file) {
    		$qrPath =substr($upload->rootPath, 1 ).$file['savepath'].$file['savename'];
    		$data=array($folder=>$qrPath);
    	}
			$image = new \Think\Image(); 
			$image->open('./'.$qrPath);//
			$image->thumb(I('post.maxW'),I('post.maxH'))->save('./'.$qrPath);
			//将图片裁剪为200x200并保存为$qrPath
			$image->open('./'.$qrPath);
			$image->crop(I('post.w'), I('post.h'),I('post.x1'),I('post.y1'))->save('./'.$qrPath);
    	$res=M('user_info')->where(array('user_id'=>$user_id))->save($data);
    	if($res){
   			$_SESSION['account']['userInfo'][$folder]=$data[$folder];
   			echo "上传成功！";
    	}else{
   			echo "上传失败，请稍后再试！";
    	}
    }
  }
























}