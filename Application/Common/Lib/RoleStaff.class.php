<?php
namespace Common\Lib;

class RoleStaff {
    
    public function setMemberUserCondition($m){
        $m->where(array("salesman_id"=> session('uid')));
    }


    public function getCustomerSearchGroup($arr){
       
        $users = array();
        $users[] = array('user_id'=>session('uid'), 'name'=> session('account')['userInfo']['realname']);
        $arr['disabled'] = true;
        $users[] = $arr;
        return $users;
    }
}