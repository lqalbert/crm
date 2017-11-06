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


/*
  如果一个客户有购买多个
  这里会出现多个相同的客户记录
  因为有购买时间 ，如果买多次 当然有多个购买时间
  这算是需求设计的问题

  关键在 customer_buy.cus_id = customers_basic.id 

*/

class ServiceSupervisorController extends CommonController{
	// protected $table = "customers_basic";
  protected $table = "Customer";
	protected $pageSize = 11;

 

  private function getRoleState(){
      $map = array(
              'supService',
              'serviceMaster',
              'gold'
          );
      return $map;
  }


	public function index(){
    $Products= D('Product')->where( array('status'=>array('NEQ', ProductModel::DELETE_STATUS)))->select();
    // $this->assign('roleTypeVal',  $this->getRoleVal());
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
    $group_id = $this->getUserGroupId();
    return D("User")->getGroupEmployee($group_id, 'user_id, realname');
    // return D('User')->getGenService('user_id,realname');
  }

  public function getList(){
    $this->setQeuryCondition();
    $count = $this->M->count();

    $this->setQeuryCondition();
    $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();
    foreach ($list as &$value) {
       //联系方式 两套
      $value['contact'] = M('customers_contacts')->where(array('cus_id'=>$value['cus_id']))->order("is_main desc")->select();

      // 跟踪员工
      $sales = M("user_info")->where(array('user_id'=>$value['salesman_id']))->field('realname,mphone')->find();
      $value['realname'] = $sales['realname'];
      $value['mphone'] = $sales['mphone'];

      $value['gen_name'] = M("user_info")->where(array('user_id'=>$value['gen_id']))->getField('realname');

    }
    $result = array('list'=>$list, 'count'=>$count);
    $this->ajaxReturn($result);
  }

  protected function setTableJoin(){
    $this->M->join("customers_basic on customers_buy.cus_id=customers_basic.id");
  }

  protected function setField(){
    $this->M->field("customers_basic.name,gen_time,gen_id,customers_basic.type,customers_basic.salesman_id,kf_time,province_name,city_name,sex,semaster_time,customers_buy.cus_id");
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
    
    $contact = I('get.contact');
    $ids = array();
    if ($contact) {
       $ids = M("customers_contacts")->where(array('qq|phone|weixin'=>array('LIKE', $val."%")))->getField("cus_id", true);
       // if ($ids) {
       //    $this->M->where(array("customers_basic.id"=>array("in", $ids)));
       //  } else {
       //    $this->M->where(array("customers_basic.id"=>-1));
       //  }
    }

    //时间区间
    $range = I("get.range");
    if ($range) {
        $dates = explode(" - ", $range);
        // $this->M->where(array('buy_time'=>array(array('EGT', $dates[0]), array("ELT", $dates[1]))));  
        $row = D("CustomerBuy")->where(array('buy_time'=>array(array('EGT', $dates[0]), array("ELT", $dates[1]))))->field("cus_id")->find();
        if ($row) {
          $ids = array_merge($ids, array_column($row, 'cus_id'));
        }
    }

    // if (isset($_GET['vt'])) {
    //   $this->M->where(array('customers_basic.type'=>'VT'));
    // }

    // $roleEname = $this->getRoleEname();
    // $map = $this->getRoleState();
    // // if(in_array($roleEname,$map)){
    // if($roleEname != RoleModel::SUP_SERVICE){
    //   $this->M->where(array('customers_basic.semaster_id'=>array('GT',0)));
    // }else{
    //   $this->M->where(array('customers_basic.semaster_id'=>session('uid')));
    // }

    $this->M->where(array('customers_basic.semaster_id'=>session('uid')));

    


    

      $user_id = I("get.user_id");
      $group_id = I("get.group_id");
      $depart_id = I("get.department_id");
      if ($user_id) {
          $this->M->where(array('customers_basic.salesman_id'=>$user_id));
      } else if($group_id){
          $user_id = D("User")->getGroupEmployee($group_id, 'id');

          if ($user_id) {
              $this->M->where(array('customers_basic.salesman_id'=>array("IN", array_column($user_id, 'id')) ));
          }  else {
              $this->M->where(array('customers_basic.salesman_id'=>-1 ));
          }

      } else if($depart_id){
          $user_id = D("User")->getDepartmentEmployee($depart_id, 'id');
          if ($user_id) {
              $user_id = array_column($user_id, 'id');
              $this->M->where(array('customers_basic.salesman_id'=>array("IN", $user_id) ));
          }  else {
              $this->M->where(array('customers_basic.salesman_id'=>-1 ));
          }
      } 

      // $this->M->group('customers_basic.id'); 

	}


  /**
  *   分配给普通
  *
  */
  public function dispacth(){
    $user_id = I('post.user_id');
    $cus_ids = I("post.cus_ids");
    
    $re  = D('Customer')->where(array('id'=>array('in', $cus_ids)))->data(array('gen_id'=>$user_id, 'gen_time'=>Date('Y-m-d H:i:s')))->save();
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
