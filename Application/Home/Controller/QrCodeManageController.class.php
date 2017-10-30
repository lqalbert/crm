<?php
namespace Home\Controller;

class QrCodeManageController extends CommonController{

  public function index(){
  	$this->assign('res',$this->getRes());
  	$this->display();
  }

  protected function getRes(){
    $user_id = session('uid');
    $qrArr = M('user_info')->where(array('user_id'=>$user_id))->field("qqqr,wxqr")->select();
    $qrArr = reset($qrArr);
    if($qrArr['qqqr'] && $qrArr['wxqr']){
      $res['qqqrImg'] = __ROOT__.$qrArr['qqqr'];
      $res['wxqrImg'] = __ROOT__.$qrArr['wxqr'];
    }else if(!$qrArr['qqqr'] && $qrArr['wxqr']){
      $res['qqqrImg'] = $qrArr['qqqr'];
      $res['wxqrImg'] = __ROOT__.$qrArr['wxqr'];
    }else if($qrArr['qqqr'] && !$qrArr['wxqr']){
      $res['qqqrImg'] = __ROOT__.$qrArr['qqqr'];
      $res['wxqrImg'] = $qrArr['wxqr'];
    }else{
      $res['qqqrImg'] = $qrArr['qqqr'];
      $res['wxqrImg'] = $qrArr['wxqr'];
    }
    return $res;

    
  }
























}