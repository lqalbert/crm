<?php
namespace Common\Lib;

use Home\Model\RoleModel;
use Home\Model\DepartmentModel;

class RoleGold {
    public function __construct(){
        // var_dump('yes');
    }


    public function getGroupContacts($obj, $id){
        $captainId = D('Role')->getIdByEname(RoleModel::CAPTAIN);
        /*return M('user_info')->where(array('role_id'=>$captainId))->select();*/

        $sql = "select user_id,mid(realname, 1, 5) as realname from user_info where (role_id=$captainId and user_id not in(select user_id from group_basic and user_id is not null) ) or user_id=$id";
        return M()->query($sql);
    }

    public function getGroupUpsOrg(){
        return D('department')->field("id,name")->select();
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
        /*$goldId = D('Role')->getIdByEname(RoleModel::GOLD);
        $m->where(array('user_info.role_id'=>array('neq', $goldId)));*/
        $depart_id = D('Role')->getIdByEname(RoleModel::DEPARTMENTMASTER);
        $m->where(array('user_info.role_id'=>array('eq', $depart_id)));
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

    public function setMemberUserCondition($m){
        
    }

    public function getCustomerSearchGroup($arr){
        return $arr;
    }
}