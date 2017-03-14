<?php
namespace Common\Lib;
use Home\Model\RoleModel;

class RoleDepartmentMaster {


    public function getDepartId($user_id){
        return M('department_basic')->where(array('user_id'=>$user_id ))->getField('id');
    }

    
    public function setEmployeeAddData($obj){
        $depart_id = $this->getDepartId($obj->id);
        $_POST['department_id'] = $depart_id;
    }

    public function getEmployeeRoleList(){

    }

    public function getGroupContacts($obj){
        $depart_id = $this->getDepartId($obj->id);
        $captainId = D('Role')->getIdByEname(RoleModel::CAPTAIN);
        $sql = "select user_id,realname from user_info where user_id not in (select user_id from group_basic ) and department_id=".$depart_id. " and role_id=".$captainId;
        return M()->query($sql);
        //return M('user_info')->where(array('department_id'=>$depart_id, 'role_id'=>$captainId, 'group_id'=>0 ))->select();
    }

    public function setEmployQueryCondition($m, $obj){
        $depart_id = $this->getDepartId($obj->id);
        $m->where(array('user_info.department_id'=>array('eq', $depart_id)));
    }

    public function getGroupUpsOrg($obj){
        return M('department_basic')->where(array('user_id'=>$obj->id ))->select();
    }

    public function setGroupQueryCondition($m, $obj){
        $depart_id = $this->getDepartId($obj->id);
        $m->where(array('group_basic.department_id'=>$depart_id));
    }

    public function getAllBenC($obj){
        $depart_id = $this->getDepartId($obj->id);
        $captainId = D('Role')->getIdByEname(RoleModel::CAPTAIN);
        if ($depart_id) {
            $sql = "select user_info.user_id,realname, group_basic.name as group_name  from user_info left join group_basic on user_info.group_id = group_basic.id where   user_info.role_id <> $captainId and user_info.department_id = ".$depart_id;

                    $members = M()->query($sql);
                    return $members;
        } else {
            return [];
        }
    }

    public function setMemberUserCondition($m){
        $subQeury = $m->field('user_id')->table('user_info')->where(array('department_id'=>session('account')['userInfo']['department_id']))->buildSql(); 
        $m->where(array("user_id"=>array('in', $subQeury)));
    }

    public function getCustomerSearchGroup($arr){
        return $arr;
    }




}