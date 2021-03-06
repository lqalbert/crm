<?php
namespace Common\Lib;
use Home\Model\RoleModel;

class RoleSpreadMaster {


    public function getDepartId($user_id){
        return M('department_basic')->where(array('user_id'=>$user_id ))->getField('id');
    }

    
    public function setEmployeeAddData($obj){
        $depart_id = $this->getDepartId($obj->id);
        $_POST['department_id'] = $depart_id;
    }

    public function getEmployeeRoleList(){

    }

    public function getGroupContacts($obj, $id){
        $depart_id = $this->getDepartId($obj->id);
        $captainId = D('Role')->getIdByEname(RoleModel::SP_CAPTAIN);
        /*return M('user_info')->where(array('department_id'=>$depart_id, 'role_id'=>$captainId, 'group_id'=>0 ))->select();*/

        $sql = "select user_id,mid(realname, 1, 5) as realname from user_info inner join rbac_user on user_info.user_id=rbac_user.id where rbac_user.status>=0 and (role_id=$captainId and user_id not in ( select user_id from group_basic where department_id = $depart_id and user_id is not null ) and department_id = $depart_id )  or user_id=$id ";
        return M()->query($sql);


    }

    public function setEmployQueryCondition($m, $obj){
        $depart_id = $this->getDepartId($obj->id);
        $m->where(array('user_info.department_id'=>array('eq', $depart_id), 'user_info.user_id'=>array('NEQ', session('uid'))));
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
        $staffId = D('Role')->getIdByEname(RoleModel::SP_STAFF);
        $departmentId = D('Role')->getIdByEname(RoleModel::SP_MASTER);
        if ($depart_id) {
            $sql = "select user_info.user_id,realname, group_basic.name as group_name  from user_info left join group_basic on user_info.group_id = group_basic.id inner join rbac_user on user_info.user_id = rbac_user.id where   (user_info.role_id = $staffId or  user_info.role_id=$departmentId)  and user_info.department_id = ".$depart_id. " and rbac_user.status > -1";

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
        foreach ($arr as $key => $value) {

            if ($value['value']=="department") {
                $arr[$key]['disabled'] = true;
            }
        }
        return $arr;
    }




}