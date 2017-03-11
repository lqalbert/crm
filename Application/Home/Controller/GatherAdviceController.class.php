<?php
namespace Home\Controller;
use Think\Model;
use Home\Model\GatherAdviceModel;
class GatherAdviceController extends CommonController{
  protected $table = 'advices_basic';
  protected $pageSize = 11;
  public function index(){
    $this->assign('AdviceType', D('GatherAdvice')->getAdviceType());
    $this->display();

  }
  
  //表格数据获取
  public function getList(){
    $this->setQeuryCondition();
    $count = (int)$this->M->count();
    $this->setQeuryCondition();
    $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
    foreach ($list as $k => $v) {
      $list[$k]['ad_user']=M('user_info')->where(array('user_id'=>$v['ad_user']))->getField('realname');
      $list[$k]['re_user']=M('user_info')->where(array('user_id'=>$v['re_user']))->getField('realname');
    }
    $result = array('list'=>$list, 'count'=>$count);
    $this->ajaxReturn($result);

  }

  //设置查询
  public function setQeuryCondition(){
    if (I('get.name')) {
      $this->M->where(array("title"=> array('like', "%".I('get.name')."%")));
    }

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
























