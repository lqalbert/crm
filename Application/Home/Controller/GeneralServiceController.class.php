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
class GeneralServiceController extends CommonController{
	protected $table = "customers_basic";
	protected $pageSize = 11;

  private function getOffset(){
    return (I('get.p',1)-1) * $this->pageSize;
  }

  private function getRoleState(){
      $map = array(
              'genService',
              'serviceMaster',
              'gold'
          );
      //return $map[$this->getRoleEname()];
      return $map;
  }

  private function getRoleVal(){
    foreach ($this->getRoleState() as $k => $v) {
      if($this->getRoleEname() == $v){
        $roleType = $k;
      }
    }
    return $roleType;
  }

	public function index(){
    $Products= D('Product')->where( array('status'=>array('NEQ', ProductModel::DELETE_STATUS)))->select();
    $this->assign('roleTypeVal',  $this->getRoleVal());
    $this->assign('Products',     $Products);
		$this->assign('customerType', D('Customer')->getType());
		$this->assign('steps',        D('CustomerLog')->getSteps());
		$this->assign('logType',      D('CustomerLog')->getType());
		$this->assign('sexType',      D('Customer')->getSexType());
		$this->display();
	}
  
	public function getList(){
		$this->setQeuryCondition();
        $count = $this->M->count();
        $this->setQeuryCondition();
        $list =  $this->M->join('left join user_info as ui on customers_basic.salesman_id=ui.user_id')
                         ->join('left join user_info as usi on customers_basic.gen_id = usi.user_id')
                         ->field('ui.realname,usi.realname as gen_name,customers_basic.*,cc.*')
                         ->order("customers_basic.id desc")
                         ->limit($this->getOffset().','.$this->pageSize)
                         ->select();
    
        $result = array('list'=>$list, 'count'=>$count);
		$this->ajaxReturn($result);
	}
 
	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
        
        $roleEname = $this->getRoleEname();
        $map = $this->getRoleState();
        if($roleEname != RoleModel::GEN_SERVICE){
          $this->M->where(array('customers_basic.gen_id'=>array('GT',0)));
        }else{
          $this->M->where(array('customers_basic.gen_id'=>session('uid')));
        }

        if (I('get.name')) {
            $this->M->where(array("customers_basic.name"=> array('like', I('get.name')."%")));
        }
        $this->M->join("customers_contacts as cc on customers_basic.id = cc.cus_id and cc.is_main = 1 ");
        if(I('get.contact')){
        	  $val=I('get.contact');
        	  $this->M->where(array('cc.qq|cc.phone|cc.weixin'=>array('LIKE',$val."%")));
        }
        

	}













}
