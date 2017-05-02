<?php
namespace Home\Controller;
use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\CustomerModel;
use Home\Model\RealInfoModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
class GeneralServiceController extends CommonController{
	protected $table = "customers_service";
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
    $this->M->where(array('gen_service'=>'1','operator_id'=>$operator_id));

    if (I('get.name')) {
        M('customers_basic as cb')->where(array("cb.name"=> array('like', I('get.name')."%")));
    }
    
    if(I('get.contact')){
    	  $val=I('get.contact');
    	  M('customers_basic as cb')->where(array('cc.qq|cc.phone|cc.weixin'=>array('LIKE',$val."%")));
    }

	}













}
