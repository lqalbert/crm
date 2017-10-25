<?php
namespace Home\Controller;

class RiskCheckGroupController extends RiskCheckController {

    public function index(){
        $this->setViewVar();
        $this->assign('employee', $this->getGroupE());
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