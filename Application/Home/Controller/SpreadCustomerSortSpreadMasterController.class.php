<?php
namespace Home\Controller;

use Home\Model\RoleModel;
use Home\Model\DepartmentModel;
//todo   fix problem 没有客户的 没有统计 部门显示只显示一条
class SpreadCustomerSortSpreadMasterController extends SpreadCustomerSortController{

   
  
    public function index(){

        $this->assign('searchGroup', array(array("name"=>"个人", "value"=>"user_id"), array("name"=>"小组", "value"=>"to_gid"),array("name"=>"部门", "value"=>"spread_id")));

        
        $this->assign("role", $this->getRoleEname());
        $this->setRoleVar();
        $this->display("SpreadCustomerSort::index");
    }


    protected function getDataAll($searchGroup){
        
        switch ($searchGroup) {
            case 'user_id': //获取推广部所有的员工

                if (!empty($this->group_id)) {
                    return $this->getGroupUser($this->group_id);
                } 


                if (!empty($this->spread_id)) {
                    return $this->getSpreadUser($this->spread_id);
                }


                $spreadDepartments = D("Department")->getSpreadDepartments("id,name");
                // $ids = array_column($spreadDepartments, 'id');
                $departStaff = array();
                
                foreach ($spreadDepartments as $key => $value) {
                    $ar = D("User")->getSpreadCommEmployee($value['id'], 'id, realname');
                    foreach ($ar as &$staff) {
                        $staff['name'] = $value['name']." - ".$staff['realname'];
                    }
                    $departStaff = array_merge($departStaff, $ar);
                }
                $re = $departStaff;
                break;
            case 'to_gid':
                if (!empty($this->spread_id)) {
                    // $groups = D("Group")->getAllGoups($this->spread_id, "id,name");
                    $re = D("Group")->join("left join department_basic as db on group_basic.department_id=db.id")
                              ->where(array('db.id'=>$this->spread_id,"group_basic.status"=>1))
                              ->field("group_basic.id, concat(db.name, ' - ',group_basic.name) as name")
                              ->select();
                } else {
                    $re = D("Group")->join("left join department_basic as db on group_basic.department_id=db.id")
                              ->where(array('db.type'=>DepartmentModel::SPREAD_DEPARTMENT,"db.status"=>1,"group_basic.status"=>1))
                              ->field("group_basic.id, concat(db.name, ' - ',group_basic.name) as name")
                              ->select();
                }
                return $re;
                break;
            case 'spread_id':
                $re = D("Department")->where(array("id"=>$this->spread_id))->select();
                break;
            default:
                $re = array();
                break;
        }
        return $re;
    }


   
}