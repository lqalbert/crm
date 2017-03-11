<?php
namespace Home\Controller;
use Think\Model;
use Home\Model\GatherAdviceModel;
class GatherAdviceController extends CommonController
{
  protected $table = 'advices_basic';
  protected $pageSize = 11;


  public function index()
  {
    $gatherAdvice = M('advices_basic');
    $advices = $gatherAdvice->select();

    $realnames = M('user_info')->where(array('role_id' => 1))->getField("user_id,realname");

    $type = array(
        "技术建议",
        "OA问题反馈",
        "系统制度",
        "其它建议",
        "软件功能"
    );
    $this->assign('adviceList', $advices);
    $this->assign('typeList', $type);
    $this->assign('realnames', $realnames);
    $this->display();
  }

  /**
   * 添加建议
   */
  public function add()
  {

    if (!empty($_POST)) {
      $gatherAdvice = M('advices_basic');
      $data = array();
      $data['type'] = I('post.type');
      $data['title'] = I('post.title');
      $data['advice'] = I('post.advice');
      $data['ad_user'] = session('account')['userInfo']['user_id'];
      $data['time'] = date('Y:m:d');
      $re = $gatherAdvice->data($data)->add();
      if ($re) {
        $this->success(L('ADD_SUCCESS'));
      } else {
        $this->error(L('ADD_ERROR'));
      }

    } else {
      $this->error(L('ADD_ERROR'));
    }
  }

  /**
   * 回复建议
   */
  public function reply(){
    $gatherAdvice = M('advices_basic');
    $data = array();
    $data['id'] = I('post.id');
    $data['reply'] = I('post.reply');
    $data['re_user'] = session('account')['userInfo']['user_id'];
    $data['re_time'] = date('Y-m-d');
    $re = $gatherAdvice->where(array('id'=>$data['id']))->data($data)->save();
    if($re){
      $this->success(L('ADD_SUCCESS'));
    }else{
      $this->error(L('ADD_ERROR'));
    }
  }

}
























