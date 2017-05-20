<?php
namespace Home\Controller;
use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\CustomerModel;
use Home\Model\RealInfoModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
use Home\Model\ProductModel;
class ServiceSupervisorController extends CommonController{
	protected $table = "customers_basic";
	protected $pageSize = 11;

  private function getOffset(){
      return (I('get.p',1)-1) * $this->pageSize;
  }

	public function index(){
    $Products= D('Product')->where( array('status'=>array('NEQ', ProductModel::DELETE_STATUS)))->select();
    $this->assign('Products', $Products);
		$this->assign('customerType', D('Customer')->getType());
    $this->assign('steps',        D('CustomerLog')->getSteps());
    $this->assign('logType',      D('CustomerLog')->getType());
		$this->assign('sexType',      D('Customer')->getSexType());
    $this->assign('GenServiceMan',$this->getGenServiceMan());
		$this->display();
	}


  public function getGenServiceMan(){
    return D('User')->getGenService('user_id,realname');
  }

  public function getList(){

    $this->setQeuryCondition();
    $count = $this->M->count();
    $this->setQeuryCondition();
  	$list = $this->M->join("customers_contacts as cc on customers_basic.id = cc.cus_id and cc.is_main = 1 ")
          ->join('left join user_info as ui on customers_basic.user_id=ui.user_id')->field('ui.realname,customers_basic.*,cc.*')
          ->order("customers_basic.id desc")->limit($this->getOffset().','.$this->pageSize)->select();
    
	  $result = array('list'=>$list, 'count'=>$count);
    $this->ajaxReturn($result);
  }

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
	/*	$operator_id=session('account')['userInfo']['user_id'];
    $this->M->where(array('service_sup'=>'1','operator_id'=>$operator_id));*/

    $gen = I('get.gen',0);
    if ($gen!=0) {
      $this->M->where(array("customers_basic.gen_id"=>array('neq', 0)));
    } else{
      $this->M->where(array("customers_basic.gen_id"=>0));
    }


    if (I('get.name')) {
        M('customers_basic as cb')->where(array("cb.name"=> array('like', I('get.name')."%")));
    }
    
    if(I('get.contact')){
    	  $val=I('get.contact');
    	  M('customers_basic as cb')->where(array('cc.qq|cc.phone|cc.weixin'=>array('LIKE',$val."%")));
    }

    $this->M->where(array('semaster_id'=>session('uid')));

	}


  /**
  *   分配给普通
  *
  */
  public function dispacth(){
    $user_id = I('post.user_id');
    $cus_ids = I("post.cus_ids");
    
    $re  = $this->M->where(array('id'=>array('in', $cus_ids)))->data(array('gen_id'=>$user_id))->save();
    if($re!==false){
      $this->success("分配成功");
    }else{
      $this->error($this->M->getError());
    }
  }













}
