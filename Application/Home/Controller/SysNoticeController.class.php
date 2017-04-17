<?php
namespace Home\Controller;

use Home\Model\SysNoticeModel;
use Home\Model\RoleModel;

class SysNoticeController extends CommonController{
  protected $table="sys_notice";
  protected $pageSize = 12;
  
  public function index(){
    $this->assign('NoticeType', $this->M->getType());


    $ename = (new RoleModel)->getEnameById(session('account')['userInfo']['role_id']);

    $this->assign('viewDecorator', $this->M->decoratorView($ename));
  	$this->display();
  }
  
  //表格数据获取
	public function getList(){
		$this->setQeuryCondition();
    $count = (int)$this->M->count();
    $this->setQeuryCondition();
    $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
    foreach ($list as $k => $v) {
    	$list[$k]['user']=M('user_info')->where(array('user_id'=>$v['user_id']))->getField('realname');
    }
    $result = array('list'=>$list, 'count'=>$count);
		$this->ajaxReturn($result);

	}
  

  //设置查询
  public function setQeuryCondition(){

		if (I('get.name')) {
			$this->M->where(array("title"=> array('like', "%".I('get.name')."%")));
		}

    $this->M->where( array('status'=>array('NEQ', SysNoticeModel::DELETE_STATUS) ) );

  }























}

























