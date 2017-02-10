<?php
namespace Common\Lib;

class RoleDepartmentMaster {
    
    public function setEmployeeAddData($obj){
        $depart_id = M('department_basic')->where(array('user_id'=>$obj->id ))->getField('id');
        $_POST['department_id'] = $depart_id;
    }

    public function getEmployeeRoleList(){

    }

    public function getGroupContacts(){
        return [];
    }

    public function setEmployQueryCondition($m, $obj){
        $depart_id = M('department_basic')->where(array('user_id'=>$obj->id ))->getField('id');
        $m->where(array('user_info.department_id'=>array('eq', $depart_id)));
    }
}