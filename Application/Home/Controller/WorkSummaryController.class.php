<?php
namespace Home\Controller;

class WorkSummaryController extends CommonController{
  protected $table="work_summary";
  protected $pageSize = 11;
  
  public function index(){
  	$this->assign('SummaryType',$this->M->getType());
  	$this->display();
  }

  //表格数据获取
	public function getList(){
    $this->setQeuryCondition();
    $count = (int)$this->M->count();
    $this->setQeuryCondition();
    $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
    foreach ($list as $k => $v) {
    	$list[$k]['su_user']=M('user_info')->where(array('user_id'=>$v['su_user']))->getField('realname');
    	$list[$k]['re_user']=M('user_info')->where(array('user_id'=>$v['re_user']))->getField('realname');
    }
    $result = array('list'=>$list, 'count'=>$count);
		$this->ajaxReturn($result);

	}

  //设置查询
  public function setQeuryCondition(){

		if (I('get.name')) {
			$this->M->where(array("content"=> array('like', "%".I('get.name')."%")));
		}

    $this->setRoleQuery();

  }


  public function addreply(){
    $_POST['re_user'] = session('uid');
    $_POST['re_time'] = Date('Y-m-d H:i:s');
    unset($_POST['su_user']);
    parent::edit();
  }



  private function setRoleQuery(){
     $funcName = $this->getRoleEname()."Condition";
     if (method_exists($this, $funcName)) {
       call_user_func(array($this,$funcName));
     } else {
       $this->commonCondition();
     }
  }

  private function commonCondition(){
    $this->M->where(array('su_user'=>session('uid')));
  }

  private function goldCondition(){
    // $this->M->where(array('su_user'=>session('uid')));
  }

  
  private function departmentMasterCondition(){
    $user_ids = D("User")->getDepartmentEmployee(session('account')['userInfo']['department_id'], 'id');
    $this->M->where(array('su_user'=>array('IN', array_column($user_ids, 'id'))));
  }



}