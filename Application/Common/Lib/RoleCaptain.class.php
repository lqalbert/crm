<?php
namespace Common\Lib;

class RoleCaptain {

    public function setMemberUserCondition($m){
    
        $userList = M('user_info')->where(array('group_id'=>session('account')['userInfo']['group_id']))->getField('user_id', true);

        $m->where(array("salesman_id"=>array('in', $userList)));
    }

    public function getCustomerSearchGroup($arr){
        $gorup_id = session('account')['userInfo']['group_id'];
        if ($gorup_id!=0) {
            $users = M('user_info')->where(array('group_id'=>$gorup_id ))->field('user_id, realname as name')->select();
            foreach ($users as $key => $value) {
                if ($value['user_id'] == session('uid')) {
                    $users[$key]['name'] = '本人';
                }
            }
            $arr['disabled'] = false;
            $users[] = $arr;
            return $users;
        } else {
            // $users[$key]['name'] = '本人';
            $users = array();
            $users[] = array('name'=>"本人");
            return $users;
        }
        
        
    }
    
}