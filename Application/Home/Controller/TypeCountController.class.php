<?php
namespace Home\Controller;

use Home\Service\CustomersTypeCount;
use Common\Lib\User;

/**
* 要考虑 一页显示所有数据
*/
class TypeCountController extends CommonController {

    protected $table = "";
    protected $pageSize = 13;
    protected $d = null;

    private function getService(){
        if ($this->d == null) {
            $this->d =  new CustomersTypeCount;
        }
        return $this->d;
    }



    private function getObjType(){
        $roleEname =  I('get.objType', $this->getRoleEname()) ;
        $map = array(
                     'gold'=>'Departments',  //总经办
                     'Departments'=>'Groups',  //总经办
                     'departmentMaster'=> 'Groups',
                     'Groups' => 'Users',
                     // 'Users'  => 'Users'
                    );
        if (isset($map[$roleEname])) {
            return $map[$roleEname];
        } else {
            return 'Groups';
        }
    }

    protected function treeOb(){
        $treeOb = new TreeController;
        return $treeOb;
    }

    //获取所有部门下拉
    public function getDeps($status){
        $treeOb = $this->treeOb();
        $arr = $treeOb->getAlldep();
        return $arr;
    }

    public function index(){
        $this->assign('Alldeps',$this->getDeps($status));
        $this->assign('objType', $this->getObjType());
        $this->assign('dist', I('get.dist',Date('Y-m-d', time())));
        $this->display();
    }

    public function getList(){
        $this->setServiceQuery();
        $list = $this->setQeuryCondition();
        //echo M()->getLastSql();die();
        $result = array('list'=>$list, 'count'=>count($list));
        $this->ajaxReturn($result);
    }

    private function setServiceQuery(){
        $sort_field = I('get.sort_field', 'id');
        $sort_order = I('get.sort_order', 'asc');

        $sort_field = empty($sort_field) ? 'id' :$sort_field;

        $this->getService()
             ->setDate(I('get.start'),I('get.end'))
             ->setOrder($sort_field." ".$sort_order)
             ->setRange(I('get.range'));
    }


    public function setQeuryCondition(){
        return $this->setCondition();
    }

    private function setCondition(){
        $this->roleEname = $this->getRoleEname();
        $funcName = $this->roleEname."Condition";
        
        if (method_exists($this, $funcName)) {
            return call_user_func(array($this, $funcName));
        }
        return array();
        
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


    private function getDepartments(){
        return $this->getService()->getDepartment();
    }

    private function captainCondition(){
        return $this->getUsers(session('account')['userInfo']['group_id']);
    }

    private function getGroups($department_id){
        return $this->getService()->getGroups($department_id);
    }

    private function getUsers($group_id){
        return $this->getService()->getUsers($group_id);
    }







}