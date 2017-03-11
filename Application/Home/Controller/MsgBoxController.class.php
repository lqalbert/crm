<?php
namespace Home\Controller;
use Home\Model\CustomerLogModel;

class MsgBoxController extends CommonController{
  protected $table="msgbox_basic";
  protected $pageSize = 11;
  
  public function index(){
  	$this->display();
  }

  //表格数据获取
  public function getList(){
    $this->setQeuryCondition();
    $count = (int)$this->M->count();
    $this->setQeuryCondition();
    $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
    foreach ($list as $k => $v) {
      $list[$k]['from_id']=M('user_info')->where(array('user_id'=>$v['from_id']))->getField('realname');
    }
    $result = array('list'=>$list, 'count'=>$count);
    $this->ajaxReturn($result);
  }


  //设置查询
  public function setQeuryCondition(){
    $this->M->where(array('to_id'=>session('account')['userInfo']['user_id']));
    if (I('get.name')) {
      $this->M->where(array("title"=> array('like', "%".I('get.name')."%")));
    }

  }



























}

























