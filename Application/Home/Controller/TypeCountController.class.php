<?php
namespace Home\Controller;

use Home\Service\CustomersTypeCount;
use Common\Lib\User;
use Home\Model\RbacUserModel;
use Home\Model\RoleModel;
/**
* 要考虑 一页显示所有数据
*/
class TypeCountController extends CommonController {

  protected $table = "";
  protected $pageSize = 15;
  protected $d = null;
  protected $where = null;   
  protected $customerType = array("A","B","C","D","F","N","V","VX","VT");
  protected $initDep = "";

  private function getService(){
    if ($this->d == null) {
        $this->d =  new CustomersTypeCount;
    }
    return $this->d;
  }


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
        array('value'=>'user','key'=>"查询队员" ),
        array('value'=>'group','key'=>"查询团组" ),
        array('value'=>'department','key'=>'查询部门')
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
    $list = $this->setQeuryCondition();
    $result = $this->setReturnArr($list);
    //$result = array('list'=>$list, 'count'=>count($list));
    $this->ajaxReturn($result);
  }

  private function setServiceQuery(){
    $sort_field = I('get.sort_field', 'id');
    $sort_order = I('get.sort_order', 'asc');

    $sort_field = empty($sort_field) ? 'id' :$sort_field;

    $this->getService()
         ->setDate(I('get.start'),I('get.end'))
         ->setOrder($sort_field." ".$sort_order)
         ->setRange(I('get.range'));
  }

  protected function setTime(){
    $start = I('get.start')." 00:00:00";
    $end = I('get.end')." 23:59:59";
    if($start && $end){
      $this->where = "and cb.created_at>='$start' and cb.created_at<='$end'";
    }
  }

  public function setQeuryCondition(){
    $type = I('get.type');
    switch (I('get.type')) {
      case 'user':
           $result = $this->setUserTypeCount($type); //基于个人为条件查询
        break;
      case 'group':
          $result = $this->setGroupTypeCount($type); //基于团组为条件查询
        break;
      case 'department':
          $result = $this->setDepTypeCount($type);//基于部门为条件查询
         break;
      default:
         $result = $this->setDepTypeCount($type); //基于部门为条件查询
        break;
    }

    return $result;
  }

  protected function setDepTypeCount($type){

    $where = null;
    $department_id = I('get.department_id');
    if(I('get.department_id')){
      $where = " and ui.department_id = $department_id";
    }

    $re = $this->getDepTypeArr($where);
    $depArr = $this->getDeps($department_id);
    $depArr = $this->commonSetTypeArr($depArr,$re,$type);
  
    return $depArr;

  }

  protected function setGroupTypeCount($type){

    $depWhere = null;
    $groupWhere = null;
    $department_id = I('get.department_id');
    $group_id = I('get.group_id');
    if(I('get.department_id')){
      $depWhere = " and ui.department_id = $department_id";
    }

    if(I('get.group_id')){
      $groupWhere = " and ui.group_id = $group_id";
    }

    $re = $this->getGroupTypeArr($depWhere,$groupWhere);
    $groupArr = $this->treeOb()->getAllGoups($department_id,$group_id);
    $groupArr = $this->commonSetTypeArr($groupArr,$re,$type);
    return $groupArr;

  }

  protected function setUserTypeCount($type){
    $depWhere = null;
    $groupWhere = null;
    $department_id = I('get.department_id');
    $group_id = I('get.group_id');
    if(I('get.department_id')){
      $depWhere = " and ui.department_id = $department_id";
    }

    if(I('get.group_id')){
      $groupWhere = " and ui.group_id = $group_id";
    }

    $re = $this->getUserTypeArr($depWhere,$groupWhere);
    $userArr = $this->getAllUser($department_id,$group_id);
    $userArr = $this->commonSetTypeArr($userArr,$re,$type);

    return $userArr;
  }

  protected function getDepTypeArr($where){
    $sql = "select count(cb.id) as c , `type` , department_id from customers_basic as cb 
    inner join user_info as ui on cb.salesman_id= ui.user_id where cb.status=1 and ui.department_id<>0 ".$this->where ." $where group by cb.type, ui.department_id";
    $re = M()->query($sql); 
    return $re;

  }

  protected function getGroupTypeArr($depWhere,$groupWhere){
    $sql = "select count(cb.id) as c , `type` , group_id from customers_basic as cb 
    inner join user_info as ui on cb.salesman_id= ui.user_id where cb.status=1 and ui.group_id<>0 ".$this->where ." $depWhere $groupWhere group by cb.type, ui.group_id";
    $re = M()->query($sql);
    return $re;

  }

  protected function getUserTypeArr($depWhere,$groupWhere){
    $sql = "select count(cb.id) as c , `type` , ui.user_id from customers_basic as cb 
    inner join user_info as ui on cb.salesman_id= ui.user_id where cb.status=1 and ui.group_id<>0 and ui.department_id<>0 ".$this->where ." $depWhere $groupWhere group by cb.type, ui.user_id";
    $re = M()->query($sql);
    return $re;

  }

  //公共处理类型方法
  protected function commonSetTypeArr($resArr,$re,$type){
    $arr = array();
    foreach ($re as $k => $v) {
      
      switch ($type) {
        case 'department':
          $arr[$v['type'].$v['department_id']] = $v['c'];
          break;
        case 'group':
          $arr[$v['type'].$v['group_id']] = $v['c'];
          break;
        case 'user':
          $arr[$v['type'].$v['user_id']] = $v['c'];
          break;
      } 
    }
    
    foreach ($resArr as $k => $v) {
      foreach ($this->customerType as $key => $value) {
        if(isset($arr[$value.$v['id']])){
          $resArr[$k][$value]=$arr[$value.$v['id']];
          $resArr[$k]['time_num']+=$arr[$value.$v['id']];

        }else{
          $resArr[$k][$value]=0;
          $resArr[$k]['time_num']+=$arr[$value.$v['id']];
        }
      }
    }

    return $resArr;
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

  












}