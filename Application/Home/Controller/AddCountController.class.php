<?php
namespace Home\Controller;


use Home\Service\CustomersGather;
use Common\Lib\User;


class AddCountController extends CommonController{
	protected $pageSize = 15;

 	protected function getSearchGroup(){
 		$searchGroup = array(
    	array('value'=>'user','key'=>"查询队员" ),
      array('value'=>'group','key'=>"查询团组" ),
      array('value'=>'department','key'=>'查询部门')
 		);

 		return $searchGroup;
 	}

	public function index(){
		$this->assign('searchGroup',$this->getSearchGroup());
		$this->display();
	}



	/**
	 * 公用 获取列表
	 *
	 * @return array() || null
	 * 
	 **/
	public function getList(){
    
		$this->d = new CustomersGather;
		$this->setServiceQuery();
    $this->getDepartmentCount();
    $this->getGroupCount();
    $this->getUserCount();
    
    if(isset($_GET['department_id']) || isset($_GET['group_id'])){
    	$result = $this->getSelectCtrl();

	  }else{
      switch (I('get.type')) {
        case 'user':
             $result = $this->setReturnArr($this->users); //基于个人为条件查询
          break;
        case 'group':
            $result = $this->setReturnArr($this->groups); //基于团组为条件查询
          break;
        case 'department':
            $result = $this->setReturnArr($this->deps);
           break;
        default:
           $result = $this->setReturnArr($this->deps); //基于部门为条件查询
          break;
      }
    }

		$this->ajaxReturn($result);
	}

	private function setServiceQuery(){
        $sort_field = I('get.sort_field', 'id');
        $sort_order = I('get.sort_order', 'asc');
        $sort_field = empty($sort_field) ? 'id' :$sort_field;
        $this->d
             ->setDate(I('get.start'), I('get.end'))
             ->setOrder($sort_field." ".$sort_order);
  }

  private function splitList($list){
  	$page = I('get.p',0);
  	$re = array_chunk($list, $this->pageSize);
  	return $re[$page-1];
  }

	/**
	* 基于部门的录入统计
	*/
	private function getDepartmentCount(){
 
    $this->deps = arr_to_map($this->d->getDepartment(),'id');
	}

	/**
	* 基于小组的录入统计
	*/
	private function getGroupCount(){
		$this->groups = arr_to_map($this->d->getAllGroups(),'id');
	}

	/**
	* 基于人的录入统计
	*/
	private function getUserCount(){
		$this->users = arr_to_map($this->d->getAllUsers(),'id');
	}

  private function setReturnArr($arr){
  	return array('list'=>$this->splitList($arr), 'count'=>count($arr));
  }


  protected function treeOb(){
  	$treeOb = new TreeController;
  	return $treeOb;
  }

  //获取所有部门下拉
  public function getDeps($status){
  	$treeOb = $this->treeOb();
  	$arr = $treeOb->getAlldep();
  	$this->ajaxReturn($arr);
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

  //判断下拉框条件
  protected function getSelectCtrl(){
    $department_id = I('get.department_id');
    $group_id = I('get.group_id');
    $type = I('get.type');

    if($type=="user" && isset($_GET['department_id']) && !isset($_GET['group_id'])){
      foreach ($this->users as $k => $v) {
        if($v['department_id'] == $department_id){
          $arr[] = $v;
        }
      }
      $result = array('list'=>$this->splitList($arr), 'count'=>count($arr));
    }elseif ($type=="user" && isset($_GET['department_id']) && isset($_GET['group_id'])) {
      $res = M('user_info')->where(array('department'=>$department_id,'group_id'=>$group_id))->getField('user_id',true);
      foreach ($res as $k => $v) {
        if(array_key_exists($v,$this->users)){

          $arr[] = $this->users[$v];
        }
      }
      $result = array('list'=>$this->splitList($arr), 'count'=>count($arr));
    }elseif ($type=="group" && isset($_GET['department_id']) && !isset($_GET['group_id'])) {
      foreach ($this->groups as $k => $v) {
        if($v['department_id'] == $department_id){
          $arr[] = $v;
        }
      }
      $result = array('list'=>$this->splitList($arr), 'count'=>count($arr));
    }elseif ($type=="group" && isset($_GET['department_id']) && isset($_GET['group_id'])) {
      $arr[] = $this->groups[$group_id];
      $result = array('list'=>$arr, 'count'=>count($arr));
    }elseif ($type=="department" && isset($_GET['department_id']) && !isset($_GET['group_id'])) {
      $arr[] = $this->deps[$department_id];
      $result = array('list'=>$arr, 'count'=>count($arr));
    }

    return $result;

  }













}


