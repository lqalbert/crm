<?php
namespace Home\Controller;

class PromotionUrlController extends CommonController{

  public function index(){
  	$this->assign('res',$this->getRes());
  	$this->display();
  }

  protected function getRes(){
    $user_id = session('uid');
    $url = "http://beta.riign.cn/promotion?id=".$user_id;
    $qrArr = M('user_info')->where(array('user_id'=>$user_id))->field("qqqr,wxqr")->select();
    $qrArr = reset($qrArr);
    if($qrArr['qqqr'] && $qrArr['wxqr']){
      $res['urlRes'] = $url;
      $res['qqqrImg'] = __ROOT__.$qrArr['qqqr'];
      $res['wxqrImg'] = __ROOT__.$qrArr['wxqr'];
    }else if(!$qrArr['qqqr'] && $qrArr['wxqr']){
      $res['urlRes'] = "请上传QQ二维码后方可生成您的推广链接！";
      $res['qqqrImg'] = $qrArr['qqqr'];
      $res['wxqrImg'] = __ROOT__.$qrArr['wxqr'];
    }else if($qrArr['qqqr'] && !$qrArr['wxqr']){
      $res['urlRes'] = "请上传微信二维码后方可生成您的推广链接！";
      $res['qqqrImg'] = __ROOT__.$qrArr['qqqr'];
      $res['wxqrImg'] = $qrArr['wxqr'];
    }else{
      $res['urlRes'] = "请上传微信、QQ二维码后方可生成您的推广链接！";
      $res['qqqrImg'] = $qrArr['qqqr'];
      $res['wxqrImg'] = $qrArr['wxqr'];
    }
    return $res;

    
  }
























}