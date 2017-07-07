<?php
namespace Cli\Controller;
use Home\Model\RoleModel;
use Home\Model\DepartmentModel;

class SaleAchievementController extends \Think\Controller{

  private $startDate = '';
  private $endDate = '';
  private $date = '';
  private $getAllData = array();

	public function index($date){
		$this->date = $date;
		$this->startDate = $date." 00:00:00";
		$this->endDate = Date('Y-m-d H:i:s', strtotime($date) + 86400);
		$this->getData();
		$this->setDataInsert();
	}
 
	private function getData(){
  	$sql = "select salesman_id, count(id) as order_num,sum(paid_in) as sale_amount from customers_order
  	        where created_at >= '".$this->startDate."' and created_at <'".$this->endDate."' group by salesman_id";
	
    $getDataArr = M()->query($sql);
    $this->getAllData = arr_to_map($getDataArr, 'salesman_id');

    //va_dump($this->getAllData);die();
	}

  private function setDataInsert(){
  	$roleM = new RoleModel();
    $allUser= M()->query("select ui.user_id,realname,gb.name as group_name, gb.id as group_id ,
    	db.name as department_name, db.id as department_id from rbac_user inner join 
    	user_info as ui on rbac_user.id = ui.user_id  left join group_basic as gb on ui.group_id=gb.id 
    	left join department_basic as db on ui.department_id=db.id where rbac_user.status>=0 
    	and ui.group_id<>0 and ui.department_id<>0 and role_id in (".
        $roleM->getIdByEname(RoleModel::CAPTAIN).",". 
        $roleM->getIdByEname(RoleModel::STAFF) .",".
        $roleM->getIdByEname(RoleModel::DEPARTMENTMASTER).") "); 
    $insertData = array();
    foreach ($allUser as $k => $v) {
    	$tmp_row = array(
        'user_id'=>$v['user_id'], 
        'group_id'=>$v['group_id'],
        'group_name'=>$v['group_name'],
        'department_id'=>$v['department_id'],
        'department_name' =>$v['department_name'],
        'date'=>$this->date,
        'order_num'=> isset($this->getAllData[$v['user_id']]) ? $this->getAllData[$v['user_id']]['order_num'] : 0,
        'sale_amount'=> isset($this->getAllData[$v['user_id']]) ? $this->getAllData[$v['user_id']]['sale_amount'] :0
    	);

      $insertData[] = $tmp_row;

    }

    if (count($insertData)>0) {
      $this->setDataSave($insertData);
    } 

  }
  

  private function setDataSave($insertData){
    $re = M('statistics_sale_achievement')->addAll($insertData);
  }



















}
