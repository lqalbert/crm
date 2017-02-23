<?php
namespace Common\Lib;

class RoleCaptain {

    public function setMemberUserCondition($m){
    
        $userList = M('user_info')->where(array('group_id'=>session('account')['userInfo']['group_id']))->getField('user_id', true);
        $m->where(array("user_id"=>array('in', $userList)));
    }

    public function getCustomerSearchGroup($arr){
        return $arr;
    }
    
}