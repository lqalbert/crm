<?php
namespace Upgrade\Controller;


class UserCreatorController extends \Think\Controller {

    
    public function index(){
        $this->deal();
    }


    public function deal(){
        $sql = "alter table user_info add column `creator` varchar(50) null;  ";
        M()->execute($sql);
    }
}