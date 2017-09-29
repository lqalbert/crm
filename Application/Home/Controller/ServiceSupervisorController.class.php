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

  private function getRoleState(){
      $map = array(
              'supService',
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
    $this->assign('Products', $Products);
		$this->assign('customerType', D('Customer')->getType());
    $this->assign('steps',        D('CustomerLog')->getSteps());
    $this->assign('logType',      D('CustomerLog')->getType());
		$this->assign('sexType',      D('Customer')->getSexType());
    $this->assign('GenServiceMan',$this->getGenServiceMan());

    $this->assign('complainTypes', D("CustomerComplain")->getType());
		$this->display();
	}


  public function getGenServiceMan(){
    return D('User')->getGenService('user_id,realname');
  }

  public function getList(){

    $this->setQeuryCondition();
    $count = $this->M->count();
    $this->setQeuryCondition();
  	$list = $this->M->join('left join user_info as ui on customers_basic.salesman_id=ui.user_id')
                   ->join('left join user_info as usi on customers_basic.semaster_id = usi.user_id')
                   ->field('ui.realname,usi.realname as semaster_name,customers_basic.*,cc.*')
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
	/*	$operator_id=session('account')['userInfo']['user_id'];
    $this->M->where(array('service_sup'=>'1','operator_id'=>$operator_id));*/

    $gen = I('get.gen',0);
    if ($gen!=0) {
      if (I("get.gen_id")) {
        $this->M->where(array("customers_basic.gen_id"=>I("get.gen_id")));
      } else {
        $this->M->where(array("customers_basic.gen_id"=>array('neq', 0)));
      }
    } else{
      $this->M->where(array("customers_basic.gen_id"=>0));
    }


    if (I('get.name')) {
        $this->M->where(array("customers_basic.name"=> array('like', I('get.name')."%")));
    }
    
    if(I('get.contact')){
    	  $val=I('get.contact');
    	  $this->M->where(array('cc.qq|cc.phone|cc.weixin'=>array('LIKE',$val."%")));
    }

    $roleEname = $this->getRoleEname();
    $map = $this->getRoleState();
    // if(in_array($roleEname,$map)){
    if($roleEname != RoleModel::SUP_SERVICE){
      $this->M->where(array('customers_basic.semaster_id'=>array('GT',0)));
    }else{
      $this->M->where(array('customers_basic.semaster_id'=>session('uid')));
    }

    $this->M->join("customers_contacts as cc on customers_basic.id = cc.cus_id and cc.is_main = 1 ");


    //时间区间
      $buys = array();
      $range = I("get.range");
      if ($range) {
          $dates = explode(" - ", $range);
          $buys = D('CustomerBuy')->where(array('buy_time'=>array(array('EGT', $dates[0]), array("ELT", $dates[1]))))->getField("user_id", true);
          
      }

      //销售部 团队 员工参数
      $user_id = I("get.user_id");
      if ($user_id) {
          $buys[] = $user_id;
          $buys = array_unique($buys);
          // $this->M->where(array('customers_basic.salesman_id'=>$user_id));
          $this->M->where(array('customers_basic.salesman_id'=>array('IN', $buys)));
          return;
      }

      $group_id = I("get.group_id");
      if ($group_id ) {
          $user_id = D("User")->getGroupEmployee($group_id, 'id');
          if ($user_id) {
              $user_id = array_column($user_id, 'id');
              $user_id = array_merge($buys, $user_id);
              $this->M->where(array('customers_basic.salesman_id'=>array("IN", $user_id) ));
          }  else {
              $this->M->where(array('customers_basic.salesman_id'=>-1 ));
          }
          return;
      }

      $depart_id = I("get.department_id");
      if ($depart_id) {
          $user_id = D("User")->getDepartmentEmployee($depart_id, 'id');
          if ($user_id) {
              $user_id = array_column($user_id, 'id');
              $user_id = array_merge($buys, $user_id);
              $this->M->where(array('customers_basic.salesman_id'=>array("IN", $user_id) ));
          }  else {
              $this->M->where(array('customers_basic.salesman_id'=>-1 ));
          }
      }

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


      /**
        * 生成弹窗消息
        */
      foreach ($cus_ids as $key => $cus_id) {
        $cusRow = D("Customer")->where(array("id"=>$cus_id))->getField("name");
        M('msg_alert')->data(
            array('to_id'=>$user_id,
                  'title'=>"您有一个新的客户",
                  'content'=>"客户：".$cusRow)
        )->add();
      }
        


      $this->success("分配成功");
    }else{
      $this->error($this->M->getError());
    }
  }


   public function getDepartms(){
        $re = D("Department")->getGoodSalesDepartments("id,name");
        if ($re) {
            $this->ajaxReturn($re);
        } else {
            $this->ajaxReturn(array());
        }
    }

    public function getGroups(){
        $depart_id = I("get.id");
        $re = D("Group")->getAllGoups($depart_id, 'id,name');
        if ($re) {
            $this->ajaxReturn($re);
        } else {
            $this->ajaxReturn(array());
        }
    }

    public function getUsers(){
        $group_id = I("get.id");
        $re = D("User")->getGroupEmployee($group_id, 'id,realname as name');
        if ($re) {
            $this->ajaxReturn($re);
        } else {
            $this->ajaxReturn(array());
        }
    }












}
