<?php
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\RealInfoModel;
use Home\Model\RbacUserModel;
class PerformanceController extends CommonController {
	protected $pageSize = 13;
  protected $dateWhere = null; 
  protected $initDep = "";
  protected $orderWhere = null;

  protected function treeOb(){
    $treeOb = new TreeController;
    return $treeOb;
  }

  protected function depSelect(){
    $ename = $this->getRoleEname();
    if ($ename == RoleModel::GOLD) {
      $department_id = "";
    }elseif($ename == RoleModel::DEPARTMENTMASTER){
      $department_id = session('account')['userInfo']['department_id']; 
      $this->initDep = $department_id;
    }
    return $this->getDeps($department_id);
  }

  protected function getSearchGroup(){
    $searchGroup = array(
        array('value'=>'user','key'=>"按队员查询" ),
        array('value'=>'group','key'=>"按团组查询" ),
        array('value'=>'department','key'=>'按部门查询')
    );

    return $searchGroup;
  }


  public function index(){
    $this->assign('Alldeps',$this->depSelect());
    $this->assign('searchGroup',$this->getSearchGroup());
    $this->assign('initDep',$this->initDep);
    $this->display();
  }


  public function getList(){
    $this->setTime();
    $this->setOrderBy();    
    $list = $this->setQeuryCondition();
    $result = $this->setReturnArr($list);
    $this->ajaxReturn($result);
  }

  protected function setTime(){
    $start = I('get.start');
    $end = I('get.end');
    $today = Date('Y-m-d');
    if($end >=  $today){
      $end = date('Y-m-d',strtotime("-1 day"));
    }
    if($start && $end){
      $this->dateWhere = "where date>='$start' and date<='$end' ";
    } else {
      $this->dateWhere = "where date>='2022-01-01' ";
    }
  }

  protected function setOrderBy(){
    $sort_field = I('get.sort_field', 'order_num');
    $sort_order = I('get.sort_order', 'desc');
    $sort_field = empty($sort_field) ? 'order_num' :$sort_field;
    $this->orderWhere = " order by $sort_field $sort_order";
  }

  public function setQeuryCondition(){
    $type = I('get.type');
    switch (I('get.type')) {
      case 'user':
           $result = $this->setUserSaleCount($type); //基于个人为条件查询
        break;
      case 'group':
          $result = $this->setGroupSaleCount($type); //基于团组为条件查询
        break;
      case 'department':
          $result = $this->setDepSaleCount($type);//基于部门为条件查询
         break;
      default:
         $result = $this->setDepSaleCount($type); //基于部门为条件查询
        break;
    }

    return $result;
  }
 
  protected function setDepSaleCount($type){
    $depWhere = null;
    $department_id = I('get.department_id');
    if(I('get.department_id')){
      $depWhere = " and department_id = $department_id";
    }

    $sql = "select department_id as id, department_name as name, sum(order_num) as order_num,sum(sale_amount) as sale_amount , sum(self_amount) as self_amount, sum(spread_amount) as spread_amount
     from statistics_sale_achievement ".$this->dateWhere." $depWhere group by department_id ".$this->orderWhere;
     // var_dump($sql);
    $re = M()->cache(true,180)->query($sql);
    return $re;
  }

  protected function setGroupSaleCount($type){
    $depWhere = null;
    $groupWhere = null;
    $department_id = I('get.department_id');
    $group_id = I('get.group_id');
    if(I('get.department_id')){
      $depWhere = " and department_id = $department_id";
    }

    if(I('get.group_id')){
      $groupWhere = " and group_id = $group_id";
    }

    $sql = "select group_id as id,department_id,department_name,concat(department_name,'-',group_name) as name, sum(order_num) as order_num,sum(sale_amount) as sale_amount  , sum(self_amount) as self_amount, sum(spread_amount) as spread_amount
     from statistics_sale_achievement ".$this->dateWhere." $depWhere $groupWhere group by group_id ".$this->orderWhere;
    $re = M()->cache(true,180)->query($sql);
    return $re;
  }

  protected function setUserSaleCount($type){
    $depWhere = null;
    $groupWhere = null;
    $department_id = I('get.department_id');
    $group_id = I('get.group_id');
    if(I('get.department_id')){
      $depWhere = " and saa.department_id = $department_id";
    }

    if(I('get.group_id')){
      $groupWhere = " and saa.group_id = $group_id";
    }

    $sql = "select saa.user_id as id,saa.department_id,saa.group_id,saa.department_name, saa.group_name ,
    concat(concat(saa.department_name,'-',saa.group_name),'-',ui.realname) as name,
    sum(saa.order_num) as order_num,sum(saa.sale_amount) as sale_amount , sum(self_amount) as self_amount, sum(spread_amount) as spread_amount from statistics_sale_achievement as saa 
    left join user_info as ui on ui.user_id=saa.user_id ".$this->dateWhere." $depWhere $groupWhere group by saa.user_id ".$this->orderWhere;
    $re = M()->cache(true,180)->query($sql);
    return $re;

  }

  private function splitList($list){
    $page = I('get.p',0);
    $re = array_chunk($list, $this->pageSize);
    return $re[$page-1];
  }

  private function setReturnArr($arr){
    return array('list'=>$this->splitList($arr), 'count'=>count($arr));
  }

  //获取所有部门下拉
  protected function getDeps($department_id){
    $treeOb = $this->treeOb();
    $arr = $treeOb->getAlldep($department_id);
    return $arr;
  }

  //获取所选属部门的小组
  public function getGroups($department_id){
    $treeOb = $this->treeOb();
    $arr = $treeOb->getAllGoups($department_id, 'id,name');
    $this->ajaxReturn($arr);
  }   

  //获取所选小组下的员工
  public function getUsers($department_id,$group_id){
    $treeOb = $this->treeOb();
    $arr = $treeOb->getGroupEmployee($department_id,$group_id, 'id,realname');
    $this->ajaxReturn($arr);
  }

  //获取部门小组的员工 缓存时间为3分钟
  protected function getAllUser($department_id=0,$group_id=0,$field="id,realname,group_id,department_id"){
    $depCtrl = null;
    $groupCtrl = null;
    if($department_id!=0 && is_numeric($department_id)){
      $depCtrl = " and ui.department_id=$department_id ";
    }

    if($group_id!=0 && is_numeric($group_id)){
      $groupCtrl = " and ui.group_id=$group_id ";
    }
    $sql = "select ru.id,ui.realname as name,ui.group_id,ui.department_id from rbac_user as ru inner join user_info as ui 
            on ru.id = ui.user_id where ui.group_id<>0 and ui.department_id<>0 and ru.status<>-1 $depCtrl $groupCtrl";
    $re= M()->query($sql);
    return $re; 
  }

  //date money  production cus_name create_name info
  public function getOrderInfo($user_id,$department_id,$start,$end){
    $startDate = $start." 00:00:00";
    $endDate  = Date('Y-m-d H:i:s', strtotime($end) + 86400);
    //原
  	$re = M('customers_order as co')->where(array('salesman_id'=>$user_id,'created_at'=>array(array('EGT',$startDate),array('LT',$endDate))))
  	->field("co.salesman_id,co.user_id,co.product_id,co.created_at as date,co.paid_in as money,
  		p.name as production,co.customer_name as cus_name,ui.realname as create_name,co.user_name,co.sale_name")
  	->join("left join products as p on p.id=co.product_id")
  	->join("left join user_info as ui on ui.user_id=co.creater_id")->select();

    //自锁
    $re2 = M('customers_order as co')->where(array('salesman_id'=>$user_id,'created_at'=>array(array('EGT',$startDate),array('LT',$endDate)), 'co.source_type'=>1))
    ->field("co.salesman_id,co.user_id,co.product_id,co.created_at as date,co.paid_in as money,
      p.name as production,co.customer_name as cus_name,ui.realname as create_name,co.user_name,co.sale_name")
    ->join("left join products as p on p.id=co.product_id")
    ->join("left join user_info as ui on ui.user_id=co.creater_id")->select();

    //推广 


    $re3 = M("customers_order")->where(array(
            'customers_order.salesman_id'=> $user_id, 
            'customers_order.created_at'=>array(array('EGT', $startDate), array('LT', $endDate))))->join("customers_buy on customers_order.buy_id = customers_buy.id")
                            ->field("customers_order.* ,customers_buy.product_name, customers_buy.buy_time");

    foreach ($re3 as &$value) {
          $user = M("user_info")->where(array('user_id'=>$value['user_id']))->field('group_id,mphone')->find();
          $value['mphone']  = $user['mphone'];
          if (!empty($value['user_name'])) {
              $tmp = explode(" ", $value['user_name']);
              $groupName = D("Group")->where(array("id"=>$user['group_id']))->getField('name');
              $value['user_name'] = $tmp[0]."-".$groupName."-".$tmp[1];
          }
          
      }


    
  	/*foreach ($re as $k => $v) {
  		if($v['salesman_id'] != $v['user_id']){
  			$re[$k]['info'] = "跟踪员工:".$v['sale_name']." 锁定员工:".$v['user_name'];
  		}else{
  			$re[$k]['info'] = "此客户为自锁自跟！";
  		}
  	}

    foreach ($re as $k => $v) {
      if($v['salesman_id'] != $v['user_id']){
        $re[$k]['info'] = "跟踪员工:".$v['sale_name']." 锁定员工:".$v['user_name'];
      }else{
        $re[$k]['info'] = "此客户为自锁自跟！";
      }
    }*/

  	$this->ajaxReturn(array('list1'=>$re, 'list2'=>$re2, 'list3'=>$re3 ));

  }



























   
}