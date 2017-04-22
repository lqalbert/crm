<?php
namespace Common\Lib;

class RoleCaptain {

    public function setMemberUserCondition($m){
    
        $userList = M('user_info')->where(array('group_id'=>session('account')['userInfo']['group_id']))->getField('user_id', true);

        $m->where(array("salesman_id"=>array('in', $userList)));
    }

    public function getCustomerSearchGroup($arr){
        foreach ($arr as $key => $value) {

            if ($value['value']=="group" || $value['value']=="department") {
                $arr[$key]['disabled'] = false;
            }
        }
        return $arr;
    }
    
}