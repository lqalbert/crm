<?php
namespace Common\Lib;

use Home\Model\RoleModel;
use Home\Model\DepartmentModel;

class RoleGold {
    public function __construct(){
        // var_dump('yes');
    }


    public function getGroupContacts(){
        $captainId = D('Role')->getIdByEname(RoleModel::CAPTAIN);
        return M('user_info')->where(array('role_id'=>$captainId))->select();
    }

    public function getGroupUpsOrg(){
        return D('department')->where(array('type'=>array('in', array(DepartmentModel::CAREER, DepartmentModel::GENERALIZE))))->field("id,name")->select();
    }

    /**
    *
    */
    /*public function getEmployeeRoleList($role_id){
        $row = M('rbac_role')->field('level')->find($role_id);
        return D('rbac_role')->where(array('level'=>array('gt', $row['level'])))->select();
    }*/

    public function setEmployeeAddData(){
        return '';
    }

    public function setEmployQueryCondition($m){

    }

    public function setGroupQueryCondition($m, $obj){
        
    }

    public function getAllBenC($obj){
        //所有的队员
        $captainId = D('Role')->getIdByEname(RoleModel::CAPTAIN);
        $sql = "select user_info.user_id,realname, group_basic.name as group_name  from user_info left join group_basic on user_info.group_id = group_basic.id  where user_info.role_id <> $captainId";
        $members = M()->query($sql);
        return $members;
    }
}