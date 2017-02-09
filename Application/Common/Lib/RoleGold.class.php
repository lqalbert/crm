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
}