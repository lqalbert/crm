<?php
namespace Upgrade\Controller;
use Think\Controller;
class FixController extends Controller {
    public function index(){
        $data =array();
        $users = M("user_info")->field("user_id, role_id")->select();
        foreach ($users as $value) {
            $data[] = array('user_id'=>$value['user_id'], 'role_id'=>$value['role_id']);
        }

        M("rbac_role_user")->addAll($data);
    }
}