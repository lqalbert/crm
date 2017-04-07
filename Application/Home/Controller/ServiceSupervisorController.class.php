<?php
namespace Home\Controller;
use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\CustomerModel;
use Home\Model\RealInfoModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
class ServiceSupervisorController extends CommonController{
	protected $table = "customers_service";
	protected $pageSize = 11;

  private function getOffset(){
      return (I('get.p',1)-1) * $this->pageSize;
  }

	public function index(){
		$this->assign('customerType', D('Customer')->getType());
		$this->assign('sexType',      D('Customer')->getSexType());
		$this->assign('GoodsType',    D('CustomerLog')->getGoodsType());
		$this->assign('ServiceCycle', D('CustomerLog')->getServiceCycle());
    $this->assign('GenServiceMan',$this->getGenServiceMan());
		$this->display();
	}


  public function getGenServiceMan(){
    $callback=M('rbac_role')->where(array('ename'=>RoleModel::GEN_SERVICE,'status'=>'1'))->find(); 
    $user_id=M('rbac_role_user')->where(array('role_id'=>$callback['id']))->getField('user_id',true);
    if($callback && $user_id){
      $man=M('user_info')->where(array('user_id'=>array('IN',$user_id)))->field('user_id,realname')->select();
    }else{
      $man=null;
    }
    return $man; 
  }

  public function getList(){
  	$this->setQeuryCondition();
	  $count=(int)$this->M->count();
	  $this->setQeuryCondition();
	  $cusArr=$this->M->getField('cus_id', true);
	  $cusList=implode(",", $cusArr);
	  if(empty($cusList)){
	    $list =null;
      $count='0';
	  }else{
	    $list = M('customers_basic as cb')->join("customers_contacts as cc on cb.id = cc.cus_id and cc.is_main = 1 ")
          ->join('left join user_info as ui on cb.user_id=ui.user_id')->field('ui.realname,cb.*,cc.*')
          ->where(array('cb.id'=>array('IN',$cusList)))->order("cb.id desc")->limit($this->getOffset().','.$this->pageSize)->select();
	    $count = $list==null ? '0' :$count;
    }
    //echo M('customers_basic as cb')->getLastSql();
	  $result = array('list'=>$list, 'count'=>$count);
    $this->ajaxReturn($result);
  }

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		$operator_id=session('account')['userInfo']['user_id'];
    $this->M->where(array('service_sup'=>'1','operator_id'=>$operator_id));

    if (I('get.name')) {
        M('customers_basic as cb')->where(array("cb.name"=> array('like', I('get.name')."%")));
    }
    
    if(I('get.contact')){
    	  $val=I('get.contact');
    	  M('customers_basic as cb')->where(array('cc.qq|cc.phone|cc.weixin'=>array('LIKE',$val."%")));
    }

	}

  /**
  *   获取跟踪信息
  *
  */
	public function trackInfo(){
    $type=D('Customer')->getType(I('post.type'));
    $arr=M('customers_log')->where(array('cus_id'=>I('post.cus_id')))->order('id desc')->select();
    foreach ($arr as $key => $value){
    	$arr[$key]['type']=$type;
    	$arr[$key]['user']=M('user_info')->where(array('user_id'=>$value['user_id']))->getField('realname');
    	$arr[$key]['name']=I('post.name');
    	$arr[$key]['track_type']=D('CustomerLog')->getType((int)$arr[$key]['track_type']);
    }
		if (IS_AJAX) {
			$this->ajaxReturn($arr);
		}  else {
			return $arr;
		}
  }   

  /**
  *   获取客户资料
  *
  */
  public function findDealInfo(){
    $arr=M('deal_info as di')->join('user_info as ui on di.user_id=ui.user_id')->field('ui.realname,di.*')
         ->where(array('di.user_id'=>I('post.user_id'),'di.cus_id'=>I('post.cus_id')))->select();
		if (IS_AJAX) {
			$this->ajaxReturn($arr);
		}  else {
			return $arr;
		}
  }

  /**
  *   获取账号信息
  *
  */
  public function softwareInfo(){
    $arr=M('software_account as sa')->join('user_info as ui on ui.user_id=sa.open_id')->field('ui.realname,sa.*')
         ->where(array('sa.user_id'=>I('post.user_id'),'sa.cus_id'=>I('post.cus_id')))->select();
		if (IS_AJAX) {
			$this->ajaxReturn($arr);
		}  else {
			return $arr;
		}
  }

  /**
  *   分配给普通
  *
  */
  public function dispacth(){
    //var_dump(I('post.'));
    $user_id=reset(I('post.'));
    array_shift($_POST);
    $userList=implode(',', I('post.'));
    $data=array(
       'service_sup'=>'0',
       'gen_service'=>'1',
       'operator_id'=>$user_id['user_id'],
    );
    $this->M->create($data);
    $re=$this->M->where(array('cus_id'=>array('IN',$userList)))->save();
    if($re){
      $this->success("分配成功");
    }else{
      $this->error($this->M->getError());
    }
  }













}
