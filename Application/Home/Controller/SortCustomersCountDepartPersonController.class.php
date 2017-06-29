<?php
namespace Home\Controller;

class SortCustomersCountDepartPersonController extends CommonController{

    public function index(){
        $this->display();
    }


    public function getList(){
        $this->department_id = session('account')['userInfo']['department_id'];
        $result = array('list'=>array(), 'count'=>0);
        $this->ajaxReturn($result);
    }
}