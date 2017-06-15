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
		$this->d =   new CustomersGather;
		$this->setServiceQuery();
		$this->getDepartmentCount();
		$this->getGroupCount();
		$this->getUserCount();

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
				 $result = $this->getDepartmentCount(); //基于部门为条件查询
				break;
		}

    if(!empty(I('get.department_id')) && empty(I('get.group_id')) && empty(I('get.user_id'))){
    	$arr[] = $this->deps[I('get.department_id')];
    	$result = array('list'=>$arr, 'count'=>count($arr));
    	//va_dump($result);die();
    }elseif (!empty(I('get.department_id')) && !empty(I('get.group_id')) && empty(I('get.user_id'))) {
    	$arr[] = $this->groups[I('get.group_id')];
    	$result = array('list'=>$arr, 'count'=>count($arr));
    }elseif(!empty(I('get.department_id')) && !empty(I('get.group_id')) && !empty(I('get.user_id'))){
    	$arr[] = $this->users[I('get.user_id')];
    	$result = array('list'=>$arr, 'count'=>count($arr));
    }
		// switch (I('get.type')) {
		// 	case 'user':
		// 	     $result = $this->getUserCount(); //基于个人为条件查询
		// 		break;
		// 	case 'group':
		// 	    $result = $this->getGroupCount(); //基于团组为条件查询
		// 		break;
		// 	case 'department':
		// 		  $result = $this->getDepartmentCount(); //基于部门为条件查询
		// 		 break;
		// 	default:
		// 		 $result = $this->getDepartmentCount(); //基于部门为条件查询
		// 		break;
		// }
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
//----------------------------------------------------------------
	/**
	* 基于部门的录入统计
	*/
	private function getDepartmentCount(){
 
    $this->deps = arr_to_map($this->d->getDepartment(),'id');
    //$this->groups = arr_group($this->getAllGroup(), "department_id");
    
		//return array('list'=>$this->splitList($list), 'count'=>count($list));
	}

	/**
	* 基于小组的录入统计
	*/
	private function getGroupCount(){
		//$list = $this->d->getAllGroups();
		$this->groups = arr_to_map($this->d->getAllGroups(),'id');
		//va_dump($list);die();
		//return array('list'=>$this->splitList($list), 'count'=>count($list));
	}

	/**
	* 基于人的录入统计
	*/
	private function getUserCount(){
		//$list = $this->d->getAllUsers();
		$this->users = arr_to_map($this->d->getAllUsers(),'id');
		//return  array('list'=>$this->splitList($list), 'count'=>count($list));
	}

  private function setReturnArr($arr){
  	return array('list'=>$this->splitList($arr), 'count'=>count($arr));
  }


//----------------------------------------------------------------

	// /**
	// * 基于人的录入统计
	// */
	// private function getUserCount(){
	// 	$list = $this->d->getAllUsers();
	// 	return  array('list'=>$this->splitList($list), 'count'=>count($list));
	// }

	/**
	* 基于小组的录入统计
	*/
	// private function getGroupCount(){
	// 	$list = $this->d->getAllGroups();
	// 	//va_dump($list);die();
	// 	return array('list'=>$this->splitList($list), 'count'=>count($list));
	// }

	/**
	* 基于部门的录入统计
	*/
	// private function getDepartmentCount(){
 
 //    $list = $this->d->getDepartment();
    
	// 	return array('list'=>$this->splitList($list), 'count'=>count($list));
	// }






  protected function treeOb(){
  	$treeOb = new TreeController;
  	return $treeOb;
  }

  //获取所有部门下拉
  public function getDeps($status){
  	$treeOb = $this->treeOb();
  	$this->ajaxReturn($treeOb->getAlldep());
  }

  //获取所选属部门的小组
  public function getGroups($department_id){
  	$treeOb = $this->treeOb();
  	$this->ajaxReturn($treeOb->getAllGoups($department_id, 'id,name'));

  }

  //获取所选小组下的员工
  public function getUsers($group_id){
  	$treeOb = $this->treeOb();
  	$this->ajaxReturn($treeOb->getGroupEmployee($group_id, 'id,realname'));
  }















}


