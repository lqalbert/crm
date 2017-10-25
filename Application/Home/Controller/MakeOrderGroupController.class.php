<?php
namespace Home\Controller;

class MakeOrderGroupController extends MakeOrderController {

    public function index(){

        $this->assign('seMaster', D('User')->getSupService());
        $this->assign('uid', session('uid'));
        $this->assign('datastaffs', $this->getGroupE());
        $this->display();
    }

    private function getGroupE(){
        //获取这一个小组的
        $group_id = $this->getUserGroupId();
        if ($group_id) {
            $users = D("User")->getGroupEmployee($group_id, 'id,realname');
            if ($users) {
                $users[] = array('id'=>"0", 'realname'=>'本组');
                return $users;
            } else {
                return array();
            }
        } else {
            return array();
        }   
    }
}