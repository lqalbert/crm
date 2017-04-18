<?php
namespace Home\Controller;
use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\CustomerModel;
use Home\Model\RealInfoModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
class RiskCtrlTwoController extends CommonController{
	protected $table = "software_account";
	protected $pageSize = 11;

  private function getOffset(){
      return (I('get.p',1)-1) * $this->pageSize;
  }

  public function index(){
    $this->assign('customerType', D('Customer')->getType());
    $this->assign('steps',        D('CustomerLog')->getSteps());
    $this->assign('logType',      D('CustomerLog')->getType());
    $this->assign('sexType',      D('Customer')->getSexType());
    $this->assign('GoodsType',    D('CustomerLog')->getGoodsType());
    $this->assign('ServiceCycle', D('CustomerLog')->getServiceCycle());
  	$this->display();
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
              ->join('left join user_info as ui on cb.user_id=ui.user_id')->field('ui.realname,cb.*,cc.*')->
              where(array('cb.id'=>array('IN',$cusList)))->order("cb.id desc")->limit($this->getOffset().','.$this->pageSize)->select();
      $count = $list==null ? '0' :$count;
	  }
	  $result = array('list'=>$list, 'count'=>$count);
    $this->ajaxReturn($result);
  }

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {

    if (I('get.name')) {
        M('customers_basic as cb')->where(array("cb.name"=> array('like', I('get.name')."%")));
    }
    
    if(I('get.contact')){
    	  $val=I('get.contact');
    	  M('customers_basic as cb')->where(array('cc.qq|cc.phone|cc.weixin|cb.type'=>array('LIKE',$val."%")));
    }

	}


  /**
  *   添加意见投诉
  *
  */
  public function addComplain(){
    $LogM = D('CustomerLog');
    $cusM = D('Customer');
    $to_type = CustomerModel::TYPE_VT;
    $data=array(
       'cus_id'=>I('post.cus_id'),
       'user_id'=>I('post.user_id'),
       'track_type'=>'11',
       'content'=>I('post.content')
    );
    $LogM->startTrans();
    if (!$LogM->create($data)) { 
        $LogM->rollback();
        return L('ADD_ERROR').$LogM->getError();
    }

    $cusM ->find($LogM->cus_id);

    if ($to_type !== "" &&  $to_type != I('post.type')) {
        $LogM->contentSetChangeType(I('post.type'), $to_type);
        $cusM ->type = $to_type;
        $re = $cusM ->save();
        if ($re === false) {
            $LogM->rollback();
            return L('ADD_ERROR').$LogM->getError()."e";
        }
    }
    if( $LogM->track_type == 0 || !empty($LogM->track_type)){
        $LogM->track_text = D('CustomerLog')->getType((int)$LogM->track_type);
    }
    if ($LogM->add()) {
        $LogM->commit();
        return L('ADD_SUCCESS');
    } else {
        $LogM->rollback();
        return L('ADD_ERROR').$LogM->getError();
    }

  }





















} 