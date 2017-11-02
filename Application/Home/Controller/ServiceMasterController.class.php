<?php
namespace Home\Controller;

use Home\Model\ProductModel;


/*
  如果一个客户有购买多个
  这里会出现多个相同的客户记录
  因为有购买时间 ，如果买多次 当然有多个购买时间
  这算是需求设计的问题

  关键在 customer_buy.cus_id = customers_basic.id 

*/

class ServiceMasterController extends ServiceSupervisorController{


    public function index(){
        $Products= D('Product')->where( array('status'=>array('NEQ', ProductModel::DELETE_STATUS)))->select();
        // $this->assign('roleTypeVal',  $this->getRoleVal());
        $this->assign('Products', $Products);
        $this->assign('customerType', D('Customer')->getType());
        $this->assign('steps',        D('CustomerLog')->getSteps());
        $this->assign('logType',      D('CustomerLog')->getType());
        $this->assign('sexType',      D('Customer')->getSexType());
        $this->assign("supService",   $this->getSupService());

        $this->assign('complainTypes', D("CustomerComplain")->getType());
            $this->display();
    }


    private function getSupService(){
        return D("User")->getSupService();
    }

    public  function getGenService(){
        $smaster_id = I("get.id");
        $group_id = M("user_info")->where(array('user_id'=>$smaster_id))->getField("group_id");
        $re = D("User")->getGroupEmployee($group_id,"user_id, realname");
        $this->ajaxReturn($re);
    }


        /**
    * 设置查询参数
    * 
    * @return null
    */
    public function setQeuryCondition() {
    /*  $operator_id=session('account')['userInfo']['user_id'];
    $this->M->where(array('service_sup'=>'1','operator_id'=>$operator_id));*/
    
    if (I("get.gen_id")) {
        $this->M->where(array("customers_basic.gen_id"=>I("get.gen_id")));
    } else {
        $this->M->where(array("customers_basic.gen_id"=>array('neq', 0)));
    }

    if (I("get.semaster_id")) {
        $this->M->where(array("customers_basic.semaster_id"=>I("get.semaster_id")));
    } else {
        $this->M->where(array("customers_basic.semaster_id"=>array('neq', 0)));
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
}