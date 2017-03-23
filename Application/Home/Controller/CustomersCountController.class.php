<?php
namespace Home\Controller;

use Home\Model\CustomerLogModel;
use Home\Model\DepartmentModel;
use Home\Model\RoleModel;
use Common\Lib\User;

/**
*
* 特性
* 针对 小组组长 部门经理 总经办
* 小组组长可以查看 本组内的每个人
* 部门经理 可以查看 小组的  个人的
* 在查看小组时 可以点击小组名  跳进这个小组内 查看这个小组内的统计
* 
* 总经办 递归 部门的功能
* 
*/


class CustomersCountController extends CommonController{

    private $user = null;
    private $roleEname = "";


    public function _initialize() {
        parent::_initialize();
        $this->user = new User();
        $this->roleEname = $this->user->getRole()['ename'];
    }



    private function getDayBetween(){
        $start = I('get.start', null);
        $end   = I('get.end',   null); 

        if (empty($start)) {
            $start = Date("Y-m-d")." 00:00:00";
        } else {
            $start = $start." 00:00:00";
        }

        if (empty($end)) {
            $end = Date("Y-m-d H:i:s", strtotime("+1 day", strtotime($start)));
        } else {
            $end = Date("Y-m-d H:i:s", strtotime("+1 day", strtotime($end." 00:00:00"))) ;
        }

        return   array('created_at'=> array( array('EGT', $start), array('LT', $end) )  );
    }


    public function getList(){
        switch (I('get.object')) {
            case 'user':
                 $result = $this->getUserCount();
                break;
            case 'group':
                 $result = $this->getGroupCount();
                break;
            case 'department':
                 $result = $this->getDepartmentCount();
                 break;
            default:
                 $result = $this->getUserCount();
                break;
        }
        $this->ajaxReturn($result);

    }
   


    public function index(){

        $org = I('get.org', 'o');
        $id  = I('get.id', '0');

        $this->assign('org', $org);
        $this->assign('id',  $id);
        $this->display();
    }

    private function getUserCondition(){
        $roleM = D('Role');
        return array(
            'role_id'=>array('in', array($roleM->getIdByEname(RoleModel::CAPTAIN), $roleM->getIdByEname(RoleModel::STAFF)))
            );
    }

    private function getUserFilterCondition(){
        $org = I('get.org', 'o');
        $id = I('get.id', 0);
        if ($org == 'group' && $id!=0) {
            return array('group_id'=>$id);
        } else {
            return array();
        }
    }

    public function goldFilter(){
        return array();
    }

    public function departmentMasterFilter(){
        
        return array('department_id'=> session('account')['userInfo']['department_id']);
    }

    public function captainFilter(){
        return array('group_id'=> session('account')['userInfo']['group_id']);
    }

    /**
    * 如果是总经办 array();
    * 如果是部门 array('departmen_id'=> $id)
    * 如果是主管 array('group_id'=>$id)
    */
    private function getUserOrgCondition(){
        return call_user_func(array($this, $this->roleEname."Filter"));
    }


    public function getUserCount(){

        $between_today = $this->getDayBetween();
        $count =  M('user_info')->where($this->getUserCondition())
                                ->where($this->getUserFilterCondition())
                                ->where($this->getUserOrgCondition())
                                ->count();

        $subQuery = M('customers_basic')->where($between_today)
                                        ->field('count(id) as `c` ,user_id')
                                        ->group('user_id')
                                        ->buildSql();

        $list = M('user_info')->field("realname as name , IFNULL(cbc.c, 0) as `count`, user_info.user_id")
                              ->join('left join '.$subQuery.' as cbc using(user_id) ')
                              ->page(I('get.p',0). ','. $this->pageSize)
                              ->where($this->getUserCondition())
                              ->where($this->getUserFilterCondition())
                              ->where($this->getUserOrgCondition())
                              ->order(' count desc')
                              ->select();
        
        $result = array('list'=>$list, 'count'=>$count);
        return $result;
    }


    private function getGroupFilterCondition(){
        $org = I('get.org', 'o');
        $id = I('get.id', 0);
        if ($org == 'department' && $id!=0) {
            return array('department'=>$id);
        } else {
            return array();
        }
    }

    public function getGroupCount(){
        $between_today = $this->getDayBetween();

        $count = M('group_basic')->where($this->getGroupFilterCondition())
                                ->where($this->getGroupOrgCondition())
                                ->count();
    }


}