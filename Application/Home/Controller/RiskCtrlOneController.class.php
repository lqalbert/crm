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
class RiskCtrlOneController extends CommonController{
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
    $this->assign('callBackMan',  $this->getCallBackMan());
		$this->display();
	}

  public function badgeNum(){
		$badgeNum['already']=$this->M->where(array('risk_id'=>session('uid'),   'risk_state'=>array('neq',0)))->count();
		$badgeNum['yet']=$this->M->where(array('risk_id'=>session('uid'), 'risk_state'=>'0'))->count();
		return $badgeNum;
  }

  public function getCallBackMan(){
    $callback=M('rbac_role')->where(array('ename'=>RoleModel::CALL_BACK,'status'=>'1'))->find();
    $user_id=M('rbac_role_user')->where(array('role_id'=>$callback['id']))->getField('user_id',true);
    if($callback && $user_id){
      $man=M('user_info')->where(array('user_id'=>array('IN',$user_id)))->field('user_id,realname')->select();
    }else{
      $man=null;
    }
    return $man; 
  }

  private function getMycust(){
    $roleName = $this->getRoleEname();

    if ($roleName!= RoleModel::GOLD) {
      $where = array('risk_id'=>session('uid'));
    }  else {
      $where = array();
    }
    
    

    switch (I('get.field')) {
      case 'already':
        $where['risk_state'] = array('neq', 0);
        break;
      case 'yet':
        $where['risk_state'] = 0;
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
	  $cusList=implode(",", $cusArr);
	  if(empty($cusList)){
	    $list =null;
      $count='0';
	  }else{
	    $list = M('customers_basic as cb')->join("customers_contacts as cc on cb.id = cc.cus_id and cc.is_main = 1 ")
              ->join('left join user_info as ui on cb.salesman_id=ui.user_id')->field('ui.realname,cb.*,cc.*')
              ->where(array('cb.id'=>array('IN',$cusList)))->order("cb.id desc")->limit($this->getOffset().','.$this->pageSize)->select();
	    $count = $list==null ? '0' :$count;
    }
	  $result = array('list'=>$list, 'count'=>$count);
      foreach ($result['list'] as $k=>$v){
          $result['list'][$k]['qq_nickname']=mb_substr($v['qq_nickname'],0,12);
          $result['list'][$k]['weixin_nickname']=mb_substr($v['weixin_nickname'],0,12);
      }
    $this->ajaxReturn($result);
  }

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		
    // $this->M->where(array('risk_one'=>'1'));

    M('customers_basic as cb')->where(array('cb.id'=>array('in', $this->getMycust())));

    if (I('get.name')) {
        M('customers_basic as cb')->where(array("cb.name"=> array('like', I('get.name')."%")));
    }
    
    if(I('get.contact')){
    	  $val=I('get.contact');
    	  M('customers_basic as cb')->where(array('cc.qq|cc.phone|cc.weixin'=>array('LIKE',$val."%")));
    }

    /*switch (I('get.field')) {
    	case 'already':
				$this->M->where(array('risk_one'=>'0'));
    		break;
    	case 'yet':
				$this->M->where(array('risk_one'=>'1'));
    		break;
    	default:
    	  $this->M->where(array('risk_one'=>'1'));
    		break;
    }*/

	}



  /**
  *   分配给回访专员
  *
  */
  public function dispacth(){
    //var_dump(I('post.'));
    $user_id=reset(I('post.'));
    array_shift($_POST);
    $userList=implode(',', I('post.'));
    $data=array(
       'risk_one'=>'0',
       'call_back'=>'1',
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

    $re =  $this->M->where(array('cus_id'=>array('in', $cus_ids), 'risk_id'=>session('uid'), 'risk_state'=>0))
            ->data(array('risk_state'=>$state))->save();
    if ($re) {
      $this->success('成功');
    } else {
      $this->error($this->M->getError());
    }
  }















}
