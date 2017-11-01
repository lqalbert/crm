<?php
namespace Home\Controller;

class QrCodeManageController extends CommonController{

  public function index(){
  	$this->assign('res',$this->getQr());
  	$this->display();
  }

  protected function getQr(){
    $user_id = session('uid');
    $qrArr = M('user_info')->where(array('user_id'=>$user_id))->field("qqqr,wxqr")->find();
    return $qrArr;  
  }
























}