<?php
namespace Home\Controller;

use Home\Model\RoleModel;
use Home\Model\DepartmentModel;
//todo   fix problem 没有客户的 没有统计 部门显示只显示一条
class SpreadCustomerSortSpreadMasterController extends SpreadCustomerSortController{

   
  
    public function index(){

        $this->assign('searchGroup', array(array("name"=>"个人", "value"=>"user_id"), array("name"=>"小组", "value"=>"to_gid")));

        
        $this->assign("role", $this->getRoleEname());
        $this->setRoleVar();
        $this->display("SpreadCustomerSort::index");
    }


   
}