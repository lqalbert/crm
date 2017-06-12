<?php
namespace Home\Controller;

use Common\Lib\GetWeek;
use Common\Lib\GetMonth;


class TestController extends CommonController {
	protected $table="department_basic";
	protected $pageSize = 1;

	public function index() {
		// $this->assign('pageSize', $this->pageSize);

		$week = new GetWeek(Date("Y-m-d"));
		$month = new GetMonth(Date("Y-m-d"));

		var_dump($week->getDay());
		var_dump($month->getDay());



		$this->display();
	}


	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		$map = array(); //查询的参数
		$map['name'] = array('like', I('get.name')."%");
		$this->M->where($map);
	}




	
	function main() {
		$this->display();
	}

	// public function xsort(){
	// 	$a = array(
	// 	  "A.准客户",
 //          "B.意向客户",
 //          "C.一般客户",
 //          "D.未有意向客户",
 //          "E.本地化客户",
 //          "N.无效客户",
 //          "F.黑名单（同行）",
 //          "v.成交客户"
 //          );

	// 	var_dump($a);
	// 	sort($a, SORT_STRING);
	// 	var_dump($a);
	// }


	public function execlTest(){
		
	}

  
  public function testTree(){
  	return 'sb';
  }


   


}