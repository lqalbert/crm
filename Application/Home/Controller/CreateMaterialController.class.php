<?php
namespace Home\Controller;
use Think\Controller;

class CreateMaterialController extends CommonController{
  
	protected $table = "promotion_material";
  protected $materialTyle = array(
              "文字",
              "图片",
              "语音",
              "视频",
              "图文"
            );
  
	public function index(){
		$this->display();
	}

  public function upImg($folder='material',$authSub=true){
    $upload = new \Think\Upload();// 实例化上传类
    $upload->maxSize   =     3145728 ;// 设置附件上传大小3M
    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    $upload->rootPath  =     './Upload/'.$folder.'/'; // 设置附件上传根目录
    $upload->autoSub   =     $authSub;
    $info   = $upload->upload();
    if(!$info) {// 上传错误提示错误信息
      $this->error($upload->getError());
    }else{// 上传成功
      $user_id=session('uid');
      $content = substr($upload->rootPath, 1 ) . $info["file"]["savepath"] . $info["file"]['savename'];
      $dataArr = array(
          "type" => 1,
          "content" => $content,
          "user_id" => $user_id,
        );
      $res=$this->M->add($dataArr);
      if($res){
        $this->ajaxReturn(array('info'=>'图片素材上传成功'));
      }else{
        $this->error($this->M->getError());
      }   
    }

  }

  public function addText(){
    $user_id = session('uid');
    $content = I('post.content');
    $dataArr = array(
        "type" => 0,
        "content" => $content,
        "user_id" => $user_id,
      );
    $data = $this->M->create($dataArr);

    if(!$data){
      $this->ajaxReturn(array("msg"=>$this->M->getError(),'code'=>1));
    }

    $result = $this->M->add();

    if(!$result){
      $this->ajaxReturn(array("msg"=>$this->M->getError(),'code'=>1));
    }else{
      $this->ajaxReturn(array("msg"=>"yes",'code'=>-1));
    }

  }













}