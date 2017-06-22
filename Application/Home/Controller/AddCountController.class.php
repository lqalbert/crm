<?php
namespace Home\Controller;


use Home\Service\CustomersGather;
use Common\Lib\User;


class AddCountController extends CommonController{
	protected $pageSize = 15;

 	protected function getSearchGroup(){
 		$searchGroup = array(
    	array('value'=>'user','key'=>"所有队员" ),
      array('value'=>'group','key'=>"所有团组" ),
      array('value'=>'department','key'=>'所有部门')
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

    if(isset($_GET['department_id']) || isset($_GET['group_id']) || isset($_GET['user_id'])){
    	$result = $this->getSelectCtrl();
	  }else{
      $result = $this->setReturnArr($this->deps);
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
    array_unshift($arr, array(
      'id'=>'department',
      'name'=>'所有部门'
    ));

  	$this->ajaxReturn($arr);
  }

  //获取所选属部门的小组
  public function getGroups($department_id){
  	$treeOb = $this->treeOb();
  	$arr = $treeOb->getAllGoups($department_id, 'id,name');
    array_unshift($arr, array(
      'id'=>'group',
      'name'=>'所有团组'
    ));
  	$this->ajaxReturn($arr);

  }

  //获取所选小组下的员工
  public function getUsers($department_id,$group_id){
  	$treeOb = $this->treeOb();
  	$arr = $treeOb->getGroupEmployee($department_id,$group_id, 'id,realname');

    array_unshift($arr, array(
      'id'=>'user',
      'realname'=>'所有队员'
    ));
  	$this->ajaxReturn($arr);
  }

  //判断下拉框条件
  protected function getSelectCtrl(){
    $department_id = I('get.department_id');
    $group_id = I('get.group_id');
    $user_id = I('get.user_id');

    if($department_id != 'department' && empty($group_id) && empty($user_id)){
    	$arr[] = $this->deps[$department_id];
    	$result = array('list'=>$arr, 'count'=>count($arr));
    }elseif ($department_id != 'department' && $group_id !='group' && empty($user_id)) {
    	$arr[] = $this->groups[$group_id];
    	$result = array('list'=>$arr, 'count'=>count($arr));
    }elseif($department_id != 'department' && $group_id !='group' && $user_id !='user'){
    	$arr[] = $this->users[$user_id];
    	$result = array('list'=>$arr, 'count'=>count($arr));
    }elseif ($department_id == 'department' && $group_id !='group' && !empty($group_id) && empty($user_id)) {
    	$arr[] = $this->groups[$group_id];
    	$result = array('list'=>$arr, 'count'=>count($arr));
    }elseif($department_id == 'department' && $group_id !='group' && $user_id =='user'){
    	$res = M('user_info')>where(array('group_id'=>$group_id))->getField('user_id',true);
    	foreach ($res as $k => $v) {
    		if(array_key_exists($v,$this->users)){
    			$arr[] = $this->users[$v];
    		}
    	}
    	$result = array('list'=>$arr, 'count'=>count($arr));
    }elseif($department_id == 'department' && $group_id !='group' && !empty($group_id) && $user_id !='user' && !empty($user_id)){
    	$arr[] = $this->users[$user_id];
    	$result = array('list'=>$arr, 'count'=>count($arr));
    }elseif ($department_id != 'department' && $group_id =='group' && empty($user_id)) {
    	foreach ($this->groups as $k => $v) {
    		if($v['department_id'] == $department_id){
    			$arr[] = $v;
    		}
    	}
    	$result = array('list'=>$this->splitList($arr), 'count'=>count($arr));
    }elseif ($department_id != 'department' && $group_id =='group' && $user_id =='user') {
    	foreach ($this->users as $k => $v) {
    		if($v['department_id'] == $department_id){
    			$arr[] = $v;
    		}
    	}
    	$result = array('list'=>$this->splitList($arr), 'count'=>count($arr));
    }elseif ($department_id != 'department' && $group_id !='group' && empty($user_id)) {


    	$arr[] = $this->groups[$group_id];
    	$result = array('list'=>$arr, 'count'=>count($arr));
    }elseif ($department_id != 'department' && $group_id !='group' && $user_id =='user') {
    	$res = M('user_info')->where(array('department'=>$department_id,'group_id'=>$group_id))->getField('user_id',true);
    	foreach ($res as $k => $v) {
    		if(array_key_exists($v,$this->users)){
    			$arr[] = $this->users[$v];
    		}
    	}
    	$result = array('list'=>$this->splitList($arr), 'count'=>count($arr));
    }elseif ($department_id == 'department' && $group_id =='group' && $user_id !='user' && !empty($user_id)) {
    	$arr[] = $this->users[$user_id];
    	$result = array('list'=>$arr, 'count'=>count($arr));
    }elseif ($department_id != 'department' && $group_id =='group' && $user_id !='user') {
    	$arr[] = $this->users[$user_id];
    	$result = array('list'=>$arr, 'count'=>count($arr));
    }elseif ($department_id == 'department' && empty($group_id) && empty($user_id)) {
      $result = $this->setReturnArr($this->deps);
    }elseif ($department_id == 'department' && $group_id =='group' && empty($user_id)){
      $result = $this->setReturnArr($this->groups);
    }elseif ($department_id == 'department' && $group_id =='group' && $user_id =='user') {
      $result = $this->setReturnArr($this->users);
    }

    return $result;

  }













}


