<?php
namespace Common\Lib;

class RoleStaff {
    
    public function setMemberUserCondition($m){
        $m->where(array("salesman_id"=> session('uid')));
    }


    public function getCustomerSearchGroup($arr){
       
        foreach ($arr as $key => $value) {

            if ($value['value']=="group") {
                $arr[$key]['disabled'] = true;
            }
        }

        return $arr;
    }
}