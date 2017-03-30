<?php
namespace Home\Controller;
use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\CustomerModel;
use Home\Model\RealInfoModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
class CallBackController extends CommonController{
	protected $table = "customers_service";
	protected $pageSize = 11;

  private function getOffset(){
      return (I('get.p',1)-1) * $this->pageSize;
  }

	public function index(){
		$groupMemberList = M('user_info')->getField("user_id,realname");
    $this->assign('memberList',   $groupMemberList);
		$this->assign('customerType', D('Customer')->getType());
		$this->assign('sexType',      D('Customer')->getSexType());
		$this->assign('GoodsType',    D('CustomerLog')->getGoodsType());
		$this->assign('ServiceCycle', D('CustomerLog')->getServiceCycle());
		$this->assign('badgeNum',     $this->badgeNum());
    $this->assign('SupServiceMan',  $this->getSupServiceMan());
		$this->display();
	}

  public function badgeNum(){
	  $cusList=M('software_account')->getField('cus_id',true);
	  $userList=M('software_account')->getField('user_id',true);
  	$user_id=session('account')['userInfo']['user_id'];
  	$badgeNum['already']=$this->M->where(array('call_back'=>'1','user_id'=>array('IN',$userList),'cus_id'=>array('IN',$cusList)))->count();
		$allCount=$this->M->where(array('call_back'=>'1','user_id'=>$user_id))->count();
		$yetNum=$allCount-$badgeNum['already'];
		$badgeNum['yet']=$yetNum>0 ? $yetNum : 0 ;
		return $badgeNum;
  }

  public function getSupServiceMan(){
    $callback=M('rbac_role')->where(array('ename'=>'sup_service','status'=>'1'))->find();
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
	  }else{
	    $list = M('customers_basic as cb')->join("customers_contacts as cc on cb.id = cc.cus_id and cc.is_main = 1 ")
          ->where(array('cb.id'=>array('IN',$cusList)))->order("cb.id desc")->limit($this->getOffset().','.$this->pageSize)->select();
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
		$user_id=session('account')['userInfo']['user_id'];
    $this->M->where(array('call_back'=>'1','user_id'=>$user_id));

    if (I('get.name')) {
        M('customers_basic as cb')->where(array("cb.name"=> array('like', I('get.name')."%")));
    }
    
    if(I('get.contact')){
    	  $val=I('get.contact');
    	  M('customers_basic as cb')->where(array('cc.qq|cc.phone|cc.weixin'=>array('LIKE',$val."%")));
    }
	  $cusList=M('software_account')->getField('cus_id',true);
	  $userList=M('software_account')->getField('user_id',true);
    switch (I('get.field')) {
    	case 'already':
				$this->M->where(array('call_back'=>'1','user_id'=>array('IN',$userList),'cus_id'=>array('IN',$cusList)));
    		break;
    	case 'yet':
				$this->M->where(array('call_back'=>'1','user_id'=>array('IN',$userList),'cus_id'=>array('NOT IN',$cusList)));
    		break;
    	default:
    	  $this->M->where(array('call_back'=>'1','user_id'=>$user_id));
    		break;
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
    $arr=M('deal_info')->where(array('user_id'=>I('post.user_id'),'cus_id'=>I('post.cus_id')))->select();
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
  public function softwareInfo(){
  	$arr=M('software_account')->where(array('user_id'=>I('post.user_id'),'cus_id'=>I('post.cus_id')))->select();
		if (IS_AJAX) {
			$this->ajaxReturn($arr);
		}  else {
			return $arr;
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
      'type'=>I('post.type'),
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
  	//var_dump($_POST);
  	$re=$this->M->where(array('cus_id'=>I('post.cus_id'),'user_id'=>I('post.user_id')))->setField('call_back','0');
  	$res=M('customers_basic')->where(array('user_id'=>I('post.user_id'),'id'=>I('post.cus_id')))->setField('type','VX');
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
       'user_id'=>$user_id['user_id']
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
