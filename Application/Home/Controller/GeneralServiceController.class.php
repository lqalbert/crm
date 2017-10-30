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
  // protected $table = "customers_basic";
	protected $table = "CustomerBuy";
	protected $pageSize = 11;

  // private function getOffset(){
  //   return (I('get.p',1)-1) * $this->pageSize;
  // }

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
    $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();
    foreach ($list as &$value) {
       //联系方式 两套
      $value['contact'] = M('customers_contacts')->where(array('cus_id'=>$value['cus_id']))->order("is_main desc")->select();

      // 跟踪员工
      $sales = M("user_info")->where(array('user_id'=>$value['salesman_id']))->field('realname,mphone')->find();
      $value['realname'] = $sales['realname'];
      $value['mphone'] = $sales['mphone'];

    }
    $result = array('list'=>$list, 'count'=>$count);
		$this->ajaxReturn($result);
	}


  private function setTableJoin(){
    $this->M->join("customers_basic on customers_buy.cus_id=customers_basic.id");
  }

  private function setField(){
    $this->M->field("customers_basic.name,gen_time,customers_basic.type,customers_basic.salesman_id,kf_time,province_name,city_name,sex,customers_buy.buy_time,customers_buy.cus_id");
  }
 
	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
        $this->setTableJoin();
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

        $contact = I('get.contact');
        if ($contact) {
           $ids = M("customers_contacts")->where(array('qq|phone|weixin'=>array('LIKE', $val."%")))->getField("cus_id", true);
           if ($ids) {
              $this->M->where(array("customers_basic.id"=>array("in", $ids)));
            } else {
              $this->M->where(array("customers_basic.id"=>-1));
            }
        }
        

        if (isset($_GET['vt'])) {
          $this->M->where(array('customers_basic.type'=>'VT'));
        }


        //时间区间
      
      $range = I("get.range");
      if ($range) {
          $dates = explode(" - ", $range);
          $this->M->where(array('buy_time'=>array(array('EGT', $dates[0]), array("ELT", $dates[1]))));  
      }

      //销售部 团队 员工参数
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
