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


    private function departmentMasterSelectFilter($arr){
        unset($arr['department']);
        return $arr;
    }

    private function captainSelectFilter($arr){
        $dArr = $this->departmentMasterSelectFilter($arr);
        unset($dArr['group']);
        return $dArr;
    }
   
    private function getSelect($arr){

        $funcName = $this->roleEname."SelectFilter";
        if (method_exists($this, $funcName)) {
            return call_user_func(array($this, $funcName),$arr);
        }
        return $arr;
    }

    public function index(){

        $org = I('get.org', 'user');
        $id  = I('get.id', '0');

        $selectArr = array(
            'department'=>'部门',
            'group'     =>'小组',
            'user'      =>'个人',
        );

        $this->assign('org', $org);
        $this->assign('id',  $id);
        $this->assign('object', $this->getSelect($selectArr));


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

    

    public function departmentMasterUserFilter(){
        
        return array('department_id'=> session('account')['userInfo']['department_id']);
    }

    public function captainUserFilter(){
        return array('group_id'=> session('account')['userInfo']['group_id']);
    }

    /**
    * 如果是总经办 array();
    * 如果是部门 array('departmen_id'=> $id)
    * 如果是主管 array('group_id'=>$id)
    */
    private function getUserOrgCondition(){
        $funcName = $this->roleEname."UserFilter";
        if (method_exists($this, $funcName)) {
            return call_user_func(array($this, $funcName));
        }
        return array();
        
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

        $list = M('user_info')->field("realname as name , IFNULL(cbc.c, 0) as `count`, user_info.user_id, 'user' as org ")
                              ->join('left join '.$subQuery.' as cbc using(user_id) ')
                              ->where($this->getUserCondition())
                              ->where($this->getUserFilterCondition())
                              ->where($this->getUserOrgCondition())
                              ->order(' count desc')
                              ->page(I('get.p',0). ','. $this->pageSize)
                              ->select();
        
        return array('list'=>$list, 'count'=>$count);
    }

    //过滤掉不需要显示的部门小组
    private function getGroupFilterCondition(){
        $org = I('get.org', 'o');
        $id = I('get.id', 0);
        if ($org == 'department' && $id!=0) {
            return array('department_id'=>$id);
        } else {
            return array();
        }
    }

    

    public function departmentMasterGroupFilter(){
        
        return array('department_id'=> session('account')['userInfo']['department_id']);
    }

    public function captainGroupFilter(){
        return array('department_id'=> -1);
    }


    //根据不同的角色 得到不同的条件
    /**
    * 如果是总经办 array();
    * 如果是部门 array('departmen_id'=> $id)
    * 如果是主管 主管没有权力查看这个 array('departmen_id'=> -1)
    */
    private function getGroupOrgCondition(){
        $funcName = $this->roleEname."GroupFilter";
        if (method_exists($this, $funcName)) {
            return call_user_func(array($this, $funcName));
        }
        return array();
    }

    public function getGroupCount(){
        $between_today = $this->getDayBetween();

        $count = M('group_basic')->where($this->getGroupFilterCondition())
                                 ->where($this->getGroupOrgCondition())
                                 ->count();
        // 小组的
        $subQuery = M('customers_basic')->join('inner join user_info as ui  using(user_id)')
                                        ->where($between_today)
                                        ->where($this->getGroupFilterCondition())
                                        ->where($this->getGroupOrgCondition())
                                        ->where(array('status'=>array('EGT',0)))
                                        ->field('count(customers_basic.id) as `c` ,ui.group_id')
                                        ->group('ui.group_id')
                                        ->buildSql();


        $list = M('group_basic')->join('left join '.$subQuery.'as uc on group_basic.id=uc.group_id')
                                ->where($this->getGroupFilterCondition())
                                ->where($this->getGroupOrgCondition())
                                ->where(array('status'=>array('EGT',0)))
                                ->field('id,name ,IFNULL(uc.c, 0) as `count` ,\'group\' as org')
                                ->order(' count desc')
                                ->page(I('get.p',0). ','. $this->pageSize)
                                ->select();
        
        return array('list'=>$list, 'count'=>$count);
    }


    //只有总经办才有权限
    private function getDepartmentCount(){
        $between_today = $this->getDayBetween();

        $count = M('department_basic')->where(array('status'=>array('EGT',0)))
                                      ->count();

        // 部门的
        $subQuery = M('customers_basic')->join('inner join user_info as ui  using(user_id)')
                                        ->where($between_today)
                                        ->where(array('status'=>array('EGT',0)))
                                        ->field('count(customers_basic.id) as `c` ,ui.department_id')
                                        ->group('ui.department_id')
                                        ->buildSql();


        $list = M('department_basic')->join('left join '.$subQuery.'as uc on department_basic.id=uc.department_id')
                                    ->where(array('status'=>array('EGT',0)))
                                    ->field('id,name ,IFNULL(uc.c, 0) as `count`, \'department\' as org ')
                                    ->order(' count desc')
                                    ->page(I('get.p',0). ','. $this->pageSize)
                                    ->select();

        return array('list'=>$list, 'count'=>$count);
    }


}