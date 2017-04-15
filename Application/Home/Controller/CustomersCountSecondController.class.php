<?php
namespace Home\Controller;

use Home\Service\CustomersCount;
use Common\Lib\User;

class CustomersCountSecondController extends CommonController {

    protected $table = "";
    protected $pageSize = 30;
    protected $d = null;

    private function getService(){
        if ($this->d == null) {
            $this->d =  new CustomersCount;
        }
        return $this->d;
    }


    public function index(){

        
        $this->display();
    }

    public function getList(){
        $this->setServiceQuery();
        $list = $this->setQeuryCondition();
        $result = array('list'=>$list, 'count'=>count($list));
        $this->ajaxReturn($result);
    }

    public function setQeuryCondition(){
        return $this->setCondition();
    }

    private function goldCondition(){
        return $this->getDepartments();
    }

    private function departmentMasterCondition(){
        return $this->getGroups(session('account')['userInfo']['department_id']);
    }

    private function captainCondition(){
        return $this->getUsers(session('account')['userInfo']['group_id']);
    }

    private function setCondition(){

        $this->user = new User();
        $this->roleEname = $this->user->getRole()['ename'];


        $funcName = $this->roleEname."Condition";
        if (method_exists($this, $funcName)) {
            return call_user_func(array($this, $funcName));
        }
        return array();
        
    }

    private function setServiceQuery(){
        $sort_field = I('get.sort_field', 'id');
        $sort_order = I('get.sort_order', 'asc');

        $sort_field = empty($sort_field) ? 'id' :$sort_field;

        $this->getService()
             ->setDate(I('get.dist', Date('Y-m-d', time()-86400)))
             ->setOrder($sort_field." ".$sort_order);
    }


    private function getDepartments(){
        return $this->getService()->getDepartment();
    }

    private function getGroups($department_id){
        return $this->getService()->getGroups($department_id);
    }

    private function getUsers($group_id){
        return $this->getService()->getGroups($group_id);
    }
}