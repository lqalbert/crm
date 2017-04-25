<?php
namespace Home\Controller;

use Home\Service\CustomersCount;
use Common\Lib\User;

/**
* 要考虑 一页显示所有数据
*/
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



    private function getObjType(){
        $roleEname =  I('get.objType', $this->getRoleEname()) ;
        $map = array(
                     'gold'=>'Departments',  //总经办
                     'Departments'=>'Groups',  //总经办


                     'departmentMaster'=> 'Groups',
                     'Groups' => 'Users'
                    );
        if (isset($map[$roleEname])) {
            return $map[$roleEname];
        } else {
            return 'Groups';
        }
    }


    public function index(){

        $this->assign('objType', $this->getObjType());
        $this->assign('dist', I('get.dist',Date('Y-m-d', time())));

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
        $objType =  I('get.objType');
        $id = I('get.id', 0);
        

        if ($id==0) {
            return $this->getDepartments();
        } else {
            
            switch ($objType) {
                case 'Groups':
                    $relate_id = M('statistics_usercustomers')->where(array('id'=>$id))->getField('department_id');
                    
                    break;
                case 'Users':
                    $relate_id = M('statistics_usercustomers')->where(array('id'=>$id))->getField('group_id');
                    break;
                default:
                    
                    break;
            }
            
            return call_user_func(array($this, "get".$objType),  $relate_id);
        }
        
    }

    private function departmentMasterCondition(){

        $objType =  I('get.objType');
        $id = I('get.id', 0);
        

        if ($id==0) {
            return $this->getGroups(session('account')['userInfo']['department_id']);
        } else {
            
            $relate_id = M('statistics_usercustomers')->where(array('id'=>$id))->getField('group_id');
            
            return call_user_func(array($this, "get".$objType),  $relate_id);
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
        return $this->getService()->getUsers($group_id);
    }
}