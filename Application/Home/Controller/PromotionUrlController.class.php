<?php
namespace Home\Controller;

class PromotionUrlController extends CommonController{

  public function index(){
  	$user_id = session('uid');
  	$url = "http://beta.riign.cn/promotion?id=".$user_id;
  	$qrArr = M('user_info')->where(array('user_id'=>$user_id))->field("qqqr,wxqr")->select();
  	$qrArr = reset($qrArr);
  	if($qrArr['qqqr'] && $qrArr['wxqr']){
    	$res = $url;
  	}else if(!$qrArr['qqqr'] && $qrArr['wxqr']){
  		$res = "请上传QQ二维码后方可生成您的推广链接！";
  	}else if($qrArr['qqqr'] && !$qrArr['wxqr']){
    	$res = "请上传微信二维码后方可生成您的推广链接！";
  	}else{
  		$res = "请上传微信、QQ二维码后方可生成您的推广链接！";
  	}
  	$this->assign('res',$res);
  	$this->display();
  }



























}