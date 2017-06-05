<?php
namespace Home\Controller;

class RiskManagerController extends CommonController{
  protected $pageSize = 12;
	protected $table = "CustomerBuy";	

  public function index(){
    $this->display();
  }

  public function setQeuryCondition(){
    $this->setQueryNameTypeCondition();
  
    $this->setCheckStatusCondition();

    $this->M->join('customers_basic as cb on customers_buy.cus_id = cb.id')
            ->join('user_info as ui on customers_buy.user_id = ui.user_id')
            ->join('user_info as usi on customers_buy.risk_id = usi.user_id')
            ->join('user_info as usin on customers_buy.callback_id = usin.user_id')
            ->join('department_basic as db on ui.department_id = db.id', 'left')
            ->field('customers_buy.id,customers_buy.user_id,customers_buy.cus_id,customers_buy.risk_state,customers_buy.callback_state,
              customers_buy.product_id,customers_buy.product_name,customers_buy.product_money,customers_buy.product_t,customers_buy.buy_time,
              ui.realname,usi.realname as risk_name,usin.realname as callback_name,db.name as department_name, cb.name as cb_name')
            ->order('customers_buy.id desc');
  }

  protected function setQueryNameTypeCondition(){
    $searchName=I('get.name',null);
    switch (I('get.queryType',null)) {
      case 'cus_name':
        $cus_id = M('customers_basic')->where(array('name'=>$searchName))->getField('id');
        $this->M->where(array('customers_buy.cus_id'=>$cus_id));
        break;
      case 'user_name':
        $user_id = M('user_info')->where(array('realname'=>$searchName))->getField('user_id');
        $this->M->where(array('customers_buy.user_id'=>$user_id));
        break;
      case 'risk_name':
        $risk_id = M('user_info')->where(array('realname'=>$searchName))->getField('user_id');
        $this->M->where(array('customers_buy.risk_id'=>$risk_id));
        break;
      case 'callback_name':
        $callback_id = M('user_info')->where(array('realname'=>$searchName))->getField('user_id');
        $this->M->where(array('customers_buy.callback_id'=>$callback_id));
        break;
      default:
        # code...
        break;
    }
  }

  protected function setCheckStatusCondition(){
    switch (I('get.checkState',null)) {
      case 'risk_passing':
        $this->M->where(array('customers_buy.risk_state'=>0));
        break;
      case 'risk_passed':
        $this->M->where(array('customers_buy.risk_state'=>1));
        break;
      case 'risk_fail':
        $this->M->where(array('customers_buy.risk_state'=>-1));
        break;
      case 'callback_passing':
        $this->M->where(array('customers_buy.risk_state'=>0));
        break;
      case 'callback_passed':
        $this->M->where(array('customers_buy.risk_state'=>1));
        break;
      case 'callback_fail':
        $this->M->where(array('customers_buy.risk_state'=>-1));
        break;
      case 'passed_fail':
        $this->M->where(array('customers_buy.risk_state'=>1,'customers_buy.callback_state'=>-1));
        break;
      case 'passed_passed':
        $this->M->where(array('customers_buy.risk_state'=>1,'customers_buy.callback_state'=>1));
        break;
      case 'fail_passed':
        $this->M->where(array('customers_buy.risk_state'=>-1,'customers_buy.callback_state'=>1));
        break;
      case 'fail_fail':
        $this->M->where(array('customers_buy.risk_state'=>-1,'customers_buy.callback_state'=>-1));
        break;
      default:
        # code...
        break;
    }
  }

  public function getList(){

    $this->setQeuryCondition();

    $count = (int)$this->M->count();

    $this->setQeuryCondition();

    $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
    // var_dump($this->M->getlastsql());
    $result = array('list'=>$list, 'count'=>$count);

    //echo $this->M->getLastSql();die;
    if (IS_AJAX) {
      $this->ajaxReturn($result);
      // $this->ajaxReturn($this->M->getLastSql());
    }  else {
      
      return $result;
    }

  }















}