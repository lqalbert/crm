<?php
namespace Home\Controller;

class RiskManagerController extends CommonController{
  protected $pageSize = 12;
	protected $table = "CustomerBuy";	

  public function index(){

    $this->assign("risks_users", D("User")->getRisk("id, realname as name"));
    $this->assign("callbacks_users", D("User")->getCallback("id, realname as name"));
    $this->assign("datastaff_users", D("User")->getDataStaff("id, realname as name"));
    $this->display();
  }

  public function setQeuryCondition(){

    $risk_state = I('get.risk_state');
    $callback_state = I('get.callback_state');
    $status     = I('get.status');
    $name       = I('get.name');

    // empty 0 与 "" 都会为 true
    if ( $risk_state == '0' || $risk_state == '1' || $risk_state == '-1' ) {
       $this->M->where(array('risk_state'=>$risk_state));
    }

    if ( $callback_state == '0' || $callback_state == '1' || $callback_state == '-1' ) {
       $this->M->where(array('callback_state'=>$callback_state));
    }

    if ( $status == '0' || $status == '1'  ) {
       $this->M->where(array('status'=>$status));
    }

    if (!empty($name)) {
      $cus_ids = D("Customer")->where(array('name'=>array('LIKE', $name.'%')))->getField('id',true);
      if ($cus_ids) {
        $this->M->where(array('cus_id'=>array('IN', $cus_ids)));
      } else {
        $this->M->where(array('cus_id'=>-1));
      }
      
    }



    //销售员工查询
    $this->setSaleCondition();

    
    //审查员工查询
    $risk_id = I("get.risk_id");
    if ($risk_id) {
      $this->M->where(array('risk_id'=>$risk_id));
    }
    //回访员工查询
    $callback_id = I("get.callback_id");
    if ($callback_id) {
      $this->M->where(array('callback_id'=>$callback_id));
    }
    //材料员工查询
    $datastaff_id = I("get.datastaff_id");
    if ($datastaff_id) {
      $this->M->where(array('datastaff_id'=>$datastaff_id));
    }

    
  }


  private function setSaleCondition(){
    $user_id   = I("get.user_id");
    $group_id  = I("get.group_id");
    $department_id = I("get.department_id");

    if ($user_id) {
       $this->M->where(array('user_id'=>$user_id));
    } else if($group_id){
      $users = D("User")->getGroupEmployee($group_id, 'id');
      if ($users) {
        $this->M->where(array('user_id'=>array('IN', array_column($users, 'id'))));
      } else {
        $this->M->where(array('user_id'=>-1));
      }
    } else if($department_id){
      $users = D("User")->getGeneralEmployee($department_id);
      if ($users) {
        $this->M->where(array('user_id'=>array('IN', array_column($users, 'id'))));
      } else {
        $this->M->where(array('user_id'=>-1));
      }
    }


  }


  private function setViewField(&$list){

    foreach ($list as &$value) {
      //客户名称
      $value['cb_name'] = D("Customer")->where(array("id"=>$value['cus_id']))->getField('name');

      //跟踪员工 部门 + 姓名
      $user = M("user_info")->where(array('user_id'=>$value['user_id']))->field('realname,department_id')->find();
      // var_dump($user);
      $db_name = D("Department")->where(array('id'=>$user['department_id']))->getField("name");
      $value['db_realname'] = $db_name." ".$user['realname'];

      //审查(风控)
      $value['risk_name'] = M("user_info")->where(array('user_id'=>$value['risk_id']))->getField('realname');
      //回访
      $value['callback_name'] = M("user_info")->where(array('user_id'=>$value['callback_id']))->getField('realname');
      //材料
      $value['datastaff_name'] = M('user_info')->where(array('user_id'=>$value['datastaff_id']))->getField('realname');

    }

  }

  

  

  public function getList(){

    $this->setQeuryCondition();

    $count = (int)$this->M->count();

    $this->setQeuryCondition();

    $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
    // var_dump($this->M->getlastsql());
      
    $this->setViewField($list);



    $result = array('list'=>$list, 'count'=>$count);


    //echo $this->M->getLastSql();die;
    if (IS_AJAX) {
      $this->ajaxReturn($result);
      // $this->ajaxReturn($this->M->getLastSql());
    }  else {
      
      return $result;
    }

  }

  public function getUsers(){
    $group_id = I("get.group_id");
    if (empty($group_id)) {
      $this->ajaxReturn(array());
    }
    $re = D("User")->getGroupEmployee(I("get.group_id"), 'id, realname as name');
    $this->ajaxReturn($re);

  }















}