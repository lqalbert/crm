<?php
namespace Cli\Controller;

use Think\Controller;

class DECController extends Controller {

    private $users = array();


    public function index(){
        $this->init();
        $this->deal();
    }


    private function init(){
        $departments = D("Home/Department")->getSpreadDepartments();
        $departmentIds = array_column($departments, 'id');

        $this->users = M("user_info")->where(array('department_id'=>array('IN', $departmentIds)))->getField('user_id', true);

    }

    private function deal(){
        $customers = M("customers_basic")->where(array('user_id'=>array('IN', $this->users), 'created_at'=>array('ELT', '2017-05-01'), 'spread_id'=>0))->select();
        echo  count($customers);
        var_dump(M("customers_basic")->getlastsql());

    }
}