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
class CallBackController extends CommonController{
	protected $table = "customers_buy";
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
		$this->assign('badgeNum',     $this->badgeNum());
    $this->assign('SupServiceMan',  $this->getSupServiceMan());
		$this->display();
	}

  public function badgeNum(){
    $badgeNum['already']=$this->M->where(array('callback_id'=>session('uid'),'callback_state'=>array('neq',0)))->distinct(true)->field('cus_id')->count();
    $badgeNum['yet']=$this->M->where(array('callback_id'=>session('uid'),'callback_state'=>'0'))->distinct(true)->field('cus_id')->count();
    return $badgeNum;
  }

  public function getSupServiceMan(){
    $callback=M('rbac_role')->where(array('ename'=>RoleModel::SUP_SERVICE,'status'=>'1'))->find();
    $user_id=M('rbac_role_user')->where(array('role_id'=>$callback['id']))->getField('user_id',true);
    if($callback && $user_id){
      $man=M('user_info')->where(array('user_id'=>array('IN',$user_id)))->field('user_id,realname')->select();
    }else{
      $man=null;
    }
    return $man; 
  }

  private function getMycust(){
    $where = array('callback_id'=>session('uid'));
    switch (I('get.field')) {
      case 'already':
        $where['callback_state'] = array('neq', 0);
        break;
      case 'yet':
        $where['callback_state'] = 0;
        break;
      default:
       
        break;
    }
    return $this->M->where($where)->getField('cus_id', true);
  }


  public function getList(){
  	$cusArr= array_keys(array_flip($this->getMycust()));
    // $this->setQeuryCondition();
    $count= count($cusArr);
	  $this->setQeuryCondition();
	  // $cusArr= $this->getMycust();
	  $cusList=implode(",", $cusArr);

	  if(empty($cusList)){
	    $list =null;
      $count='0';
	  }else{
	    $list = M('customers_basic as cb')->join("customers_contacts as cc on cb.id = cc.cus_id and cc.is_main = 1 ")
          ->join('left join user_info as ui on cb.salesman_id=ui.user_id')->field('ui.realname,cb.*,cc.*')
          ->where(array('cb.id'=>array('IN',$cusList)))->order("cb.id desc")->limit($this->getOffset().','.$this->pageSize)->select();
        // var_dump(M('customers_basic as cb')->getlastsql());
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
		/*$operator_id=session('account')['userInfo']['user_id'];
    $this->M->where(array('call_back'=>'1','operator_id'=>$operator_id));*/

    M('customers_basic as cb')->where(array('cb.id'=>array('in', $this->getMycust())));

    if (I('get.name')) {
        M('customers_basic as cb')->where(array("cb.name"=> array('like', I('get.name')."%")));
    }
    
    if(I('get.contact')){
    	  $val=I('get.contact');
    	  M('customers_basic as cb')->where(array('cc.qq|cc.phone|cc.weixin'=>array('LIKE',$val."%")));
    }
	  $cusList=M('software_account')->getField('cus_id',true);
	  $operatorList=M('software_account')->getField('open_id',true);
    switch (I('get.field')) {
    	case 'already':
        if($cusList==null || $operatorList==null){
          $this->M->where(array('call_back'=>array('GT','1')));
        }else{
          $this->M->where(array('call_back'=>'1','operator_id'=>array('IN',$operatorList),'cus_id'=>array('IN',$cusList)));
        }
    		break;
    	case 'yet':
        if($cusList==null || $operatorList==null){
          $this->M->where(array('call_back'=>array('GT','1')));
        }else{
          $this->M->where(array('call_back'=>'1','operator_id'=>array('IN',$operatorList),'cus_id'=>array('NOT IN',$cusList)));
        }
				
    		break;
    	default:
    	   $this->M->where(array('call_back'=>'1','operator_id'=>$operator_id));
    		break;
    }

	}


  /**
  *   开设软件账号
  *
  */
  public function openAccount(){
  	//var_dump(I('post.'));
  	$open_id=session('account')['userInfo']['user_id'];
  	$data=array(
      'user_id'=>I('post.user_id'),
      'cus_id'=>I('post.cus_id'),
      'open_id'=>$open_id,
      'account_id'=>I('post.accountID'),
      'pdt_id'=>I('post.pdt_id'),
      'mark'=>I('post.mark')
  	);
    M('software_account')->create($data);
    $re=M('software_account')->add();
    if($re){
      $this->success("开设成功");
    }else{
      $this->error($this->M->getError());
    }

  }

  /**
  *   审核未通过
  *
  */
  public function reviewFail(){
  	$operator_id=session('account')['userInfo']['user_id'];
  	$re=$this->M->where(array('operator_id'=>$operator_id,'cus_id'=>I('post.cus_id'),'user_id'=>I('post.user_id')))->setField('call_back','0');
  	$res=M('customers_basic')->where(array('user_id'=>I('post.user_id'),'id'=>I('post.cus_id')))->setField('type',CustomerModel::TYPE_VX);
    if($re && $res){
      $this->success("审核通过");
    }else{
      $this->error($this->M->getError().M('customers_basic')->getError());
    }
  }

  /**
  *   分配给客服主管
  *
  */
  public function dispacth(){
    //var_dump(I('post.'));
    $user_id=reset(I('post.'));
    array_shift($_POST);
    $userList=implode(',', I('post.'));
    $data=array(
       'call_back'=>'0',
       'service_sup'=>'1',
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







   public function check(){
    $cus_ids  = I("post.cus_ids");
    $state = I("post.state");

    $re =  $this->M->where(array('cus_id'=>array('in', $cus_ids), 'callback_id'=>session('uid'), 'callback_state'=>0))
            ->data(array('callback_state'=>$state))->save();
    if ($re) {
      $this->success('成功');
    } else {
      $this->error($this->M->getError());
    }
  }














}
