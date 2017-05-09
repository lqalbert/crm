<?php
namespace Home\Controller;


use Home\Service\CustomersGather;
use Common\Lib\User;


class AddCountController extends CommonController{
	protected $pageSize = 15;



	public function index(){

		$this->assign('searchGroup',array(array('value'=>'user','key'=>"个人" ) ,
       array('value'=>'group','key'=>"团组" ),array('value'=>'department','key'=>'部门')));
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
		switch (I('get.type')) {
			case 'user':
			     $result = $this->getUserCount(); //基于个人为条件查询
				break;
			case 'group':
			    $result = $this->getGroupCount(); //基于团组为条件查询
				break;
			case 'department':
				  $result = $this->getDepartmentCount(); //基于部门为条件查询
				 break;
			default:
				 $result = $this->getDepartmentCount(); //基于部门为条件查询
				break;
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
	* 基于人的录入统计
	*/
	private function getUserCount(){
		$list = $this->d->getAllUsers();
		return  array('list'=>$this->splitList($list), 'count'=>count($list));
	}
	/**
	* 基于小组的录入统计
	*/
	private function getGroupCount(){
		$list = $this->d->getAllGroups();
		return array('list'=>$this->splitList($list), 'count'=>count($list));
	}
	/**
	* 基于部门的录入统计
	*/
	private function getDepartmentCount(){
        $list = $this->d->getDepartment();

		return array('list'=>$this->splitList($list), 'count'=>count($list));
	}



}


