<?php
namespace Home\Controller;
use Home\Service\CustomersGather;
use Common\Lib\User;


// getBetween不正确
// 有问题 
// 直接使用了id 来判断 这个class要禁用
class SortCustomersCountController extends CommonController{
    protected $table = "";
    protected $pageSize = 30;
    protected $d = null;

    private function getObjType(){
        $roleEname =  I('get.objType', $this->getRoleEname()) ;
        $map = array(
                     'gold'=>'Departments',  //总经办
                     'Departments'=>'Groups',  //总经办


                     'departmentMaster'=> 'Groups',
                     'Groups' => 'Users',

                     'captain' => 'Users'
                    );

        if (isset($map[$roleEname])) {
            return $map[$roleEname];
        } else {
            return 'Groups';
        }
    }


    public function index(){
        
        $this->assign('objType', $this->getObjType());
        $this->assign('start', I('get.start',''));
        $this->assign('end',   I('get.end',''));
        $this->display();
    }

    public function getList(){
        $this->d =   new CustomersGather;
        $this->setServiceQuery();
        $list = $this->setQeuryCondition();
        $result = array('list'=>$list, 'count'=>count($list));
        $this->ajaxReturn($result);
    }

    public function setQeuryCondition(){
        return $this->setCondition();
    }

    private function goldCondition(){
        $objType =  I('get.objType');
        $id = I('get.id', 0);
    
        if ($id==0) {
            return $this->getDepartments();
        } else {

            return call_user_func(array($this, "get".$objType),  $id);
        }
        
    }

    private function departmentMasterCondition(){

        $objType =  I('get.objType');
        $id = I('get.id', 0);
        
        if ($id==0) {
            return $this->getGroups(session('account')['userInfo']['department_id']);
        } else {
            return call_user_func(array($this, "get".$objType),  $id);
        }
    }

    private function captainCondition(){
        return $this->getUsers(session('account')['userInfo']['group_id']);
    }

    private function setCondition(){
        $this->roleEname = $this->getRoleEname();
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
        $this->d
             ->setDate(I('get.start'), I('get.end'))
             ->setOrder($sort_field." ".$sort_order);
    }


    private function getDepartments(){
        return $this->d->getDepartment();
    }

    private function getGroups($department_id){
        return $this->d->getGroups($department_id);
    }

    private function getUsers($group_id){
        return $this->d->getUsers($group_id);
    }
}


