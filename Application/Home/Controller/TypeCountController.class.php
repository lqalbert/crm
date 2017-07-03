<?php
namespace Home\Controller;

use Home\Service\CustomersTypeCount;
use Common\Lib\User;

/**
* 要考虑 一页显示所有数据
*/
class TypeCountController extends CommonController {

  protected $table = "";
  protected $pageSize = 15;
  protected $d = null;
  protected $where = null;

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


  protected function getSearchGroup(){
    $searchGroup = array(
        array('value'=>'user','key'=>"查询队员" ),
        array('value'=>'group','key'=>"查询团组" ),
        array('value'=>'department','key'=>'查询部门')
    );

    return $searchGroup;
  }

  public function index(){
    $this->assign('Alldeps',$this->getDeps($status));
    $this->assign('searchGroup',$this->getSearchGroup());
    $this->display();
  }

  public function getList(){
    //$this->setServiceQuery();
    $this->setTime();
    $list = $this->setQeuryCondition();
    $result = $this->setReturnArr($list);
    //echo M()->getLastSql();die();
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
    switch (I('get.type')) {
      case 'user':
           $result = $this->setReturnArr($this->users); //基于个人为条件查询
        break;
      case 'group':
          $result = $this->setReturnArr($this->groups); //基于团组为条件查询
        break;
      case 'department':
          $result = $this->setDepTypeCount();//基于部门为条件查询
         break;
      default:
         $result = $this->setDepTypeCount(); //基于部门为条件查询
        break;
    }

    return $result;
  }


  protected function setDepTypeCount(){
    $sql = "select count(cb.id) as c , `type` , department_id from customers_basic as cb 
    inner join user_info as ui on cb.salesman_id= ui.user_id where cb.status=1 and ui.department_id<>0 ".$this->where ."group by cb.type, ui.department_id";
    $re = M()->query($sql);
    $depArr = $this->getDeps($status);
    $arr = array();
    $customerType = array("A","B","C","D","F","N","V","VX","VT");
    foreach ($re as $k => $v) {
      $arr[$v['type'].$v['department_id']] = $v['c'];
    }
    
    foreach ($depArr as $k => $v) {
      foreach ($customerType as $key => $value) {
        if(isset($arr[$value.$v['id']])){
          $depArr[$k][$value]=$arr[$value.$v['id']];
          $depArr[$k]['time_num']+=$arr[$value.$v['id']];

        }else{
          $depArr[$k][$value]=0;
          $depArr[$k]['time_num']+=$arr[$value.$v['id']];
        }
      }
    }
    //va_dump($depArr);die();
  
    return $depArr;

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
  protected function getDeps($status){
    $treeOb = $this->treeOb();
    $arr = $treeOb->getAlldep();
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






}