<?php
namespace Home\Service;

use Cli\Service\CustomerCountServiceModel;
use Home\Model\DepartmentCustomerStatisticsModel;
use Home\Model\GroupCustomerStatisticsModel;
use Home\Model\GroupCustomerStatisticsAllModel;
use Home\Model\UserCustomerStatisticsModel;
use Home\Model\UserCustomerStatisticsAllModel;
use Home\Model\UserCustomerStatisticsDepartmentModel;
use Common\Lib\GetWeek;
use Common\Lib\GetMonth;

class CustomersGather {

    private $start =   '';
    private $end   =   '';
    private $order = 'id asc';

    private $today = '';


    public function __construct(){
        $this->today = Date('Y-m-d');
    }



    private function getFields(){
        return array('create_num', 'today_v', 'conflict_to', 'conflict_from', 'pulls_num');
    }

    public function setOrder($order){
        $this->order = $order;

        $fields  = explode(' ', $this->order);
        $this->field = $fields[0];
        $sortMap = array('asc'=>SORT_ASC, 'desc'=>SORT_DESC );
        $this->sort = $sortMap[$fields[1]];
        

        return $this;
    }

    public function setDate($start, $end){
        $this->start = $start;
        $this->end   = $end;

        return $this;
    }



    private function getSqlFields(){
        $field = $this->getFields();
        $arr = array();
        foreach ($field as $key => $value) {
            $arr[] = "sum(`". $value . "`) as ".$value;
        }
        return implode(",", $arr);
    }

    private function getTodayDepartment(){
        $d = new DepartmentCustomerStatisticsModel;
        $n = new CustomersCountToday($d);
        // $n->setOrder($this->order);
        return $n->index($this->today);
    }

    private function getTodayGroups($department_id){
        $d = new GroupCustomerStatisticsModel($department_id);
        $n = new CustomersCountToday($d);
        // $n->setOrder($this->order);
        return $n->index($this->today);
    }

    private function getTodayGroupsAll(){
        $d = new GroupCustomerStatisticsAllModel();
        $n = new CustomersCountToday($d);
        // $n->setOrder($this->order);
        return $n->index($this->today);
    }

    private function getTodayUsers($group_id){
        $d = new UserCustomerStatisticsModel($group_id);
        $n = new CustomersCountToday($d);
        // $n->setOrder($this->order);
        return $n->index($this->today);
    }

    private function getTodayUsersAll(){
        $d = new UserCustomerStatisticsAllModel();
        $n = new CustomersCountToday($d);

        return $n->index($this->today);

    }

    private function getDepartmentTodayUsersAll($department_id){
        $d = new UserCustomerStatisticsDepartmentModel($department_id);
        $n = new CustomersCountToday($d);

        return $n->index($this->today);

    }


    // $list 是历史纪录
    // $list2 是当天的纪录
    // 如果  $list 里有 面 $list2里面没有  ?
    // 如果 $list2 里面 有而 $list里面有 ？

    // 以 $list 为准 ，如果 $list2里有而 $list没有  用 array_diff_key 计算差集 后 push 到 $list里
    private function mergeList($list, $list2){
        $list2 = arr_to_map($list2, 'id');
         // is_numeric
        foreach ($list as $key => $value) {
            if (isset($list2[$value['id']])) {
                foreach ($value as $field => $val) {
                    if ($field != 'id' && is_numeric($val)) {
                        $list[$key][$field] += $list2[$value['id']][$field];
                    }
                }   
            }
        }

        $list = arr_to_map($list, 'id');
        $delt = array_diff_key($list2, $list);
        $list = array_values($list);
        if ($delt) {
           $list = array_merge($list, $delt); 
        }
        return $list;
    }

    public function reSort($re){
        $columen = array_column($re, $this->field);
        array_multisort($columen, $this->sort , SORT_NUMERIC, $re);
        return $re;
    }



    public function getDepartment(){
        if ($this->end >=  $this->today ) {

            $toDaylist =  $this->getTodayDepartment();
        } else {
            $toDaylist = array();
        }

        $list  = M('statistics_usercustomers')->field($this->getSqlFields().", department_id as id, department_name as name")
                                                            ->where(array('date'=> array(array('EGT',$this->start),array('ELT',$this->end))))
                                                            ->group('department_id')
                                                            // ->order($this->order)
                                                            ->select();    

                                                                                    
        $list  = $this->mergeList($list, $toDaylist);

        return $this->reSort($list);
    }

    public function getGroups($department_id = 0){
        if ($this->end >=  $this->today) {
            $toDaylist = $this->getTodayGroups($department_id); 
        } else {
            $toDaylist = array();
        }


        $list2 = M('statistics_usercustomers')->field($this->getSqlFields().",group_id as id , group_name as name")
                                                ->where(array('department_id'=>$department_id ))
                                                ->where(array('date'=> array(array('EGT',$this->start),array('ELT',$this->end))))
                                                ->group('group_id')
                                                ->select();


        $list  = $this->mergeList($list2, $toDaylist);
        return $this->reSort($list);
    }


    public function getUsers($group_id){
        if ($this->end >=  $this->today) {
            $toDaylist = $this->getTodayUsers($group_id); 
        } else {
            $toDaylist = array();   
        }
        // statistics_usercustomers.id 改成 statistics_usercustomers.user_id as id
        // $this->mergeList 这里面会跟据id 合并
        $list2 = M('statistics_usercustomers')->join("user_info as ui on statistics_usercustomers.user_id=ui.user_id")
                                                ->field($this->getSqlFields().", statistics_usercustomers.user_id as id, realname as name")
                                                ->where(array('statistics_usercustomers.group_id'=>$group_id ))
                                                ->where(array('date'=> array(array('EGT',$this->start),array('ELT',$this->end))))
                                                ->group('statistics_usercustomers.user_id')
                                                ->select();
        $list  = $this->mergeList($list2, $toDaylist);
        return $this->reSort($list);
    }

    /**
    * 包装一个部门
    */
    private function wrapDepartment($department_id, $department_name, $list){
        foreach ($list as $key => $value) {
            $list[$key]['department_id'] = $department_id;
            $list[$key]['department_name'] = $department_name;
        }
        return $list;
    }

    public function getAllGroups(){
        $toDaylist = array();   
        if ($this->end >=  $this->today) {

            $toDaylist = $this->getTodayGroupsAll();
            $departments = D('Department')->getSalesDepartments();
            $departmentsMap = arr_to_map($departments, 'id');
            foreach ($toDaylist as $key => $value) {
                $toDaylist[$key]['department_name'] = $departmentsMap[$value['department_id']]['name'];
            }
            /*foreach ($departments as $value) {
                $toDaylist = array_merge($toDaylist, $this->wrapDepartment($value['id'], $value['name'], $this->getTodayGroups($value['id'])));
            }*/
        } 


        $list2 = M('statistics_usercustomers')->field($this->getSqlFields().",statistics_usercustomers.group_id as id , group_name as name, statistics_usercustomers.department_id, department_name, ui.realname")
                                              ->join('group_basic as gb on statistics_usercustomers.group_id = gb.id ', 'left')
                                              ->join('user_info as ui on gb.user_id = ui.user_id', 'left')
                                                ->where(array('date'=> array(array('EGT',$this->start),array('ELT',$this->end))))
                                                ->group('statistics_usercustomers.group_id')
                                                ->select();
        $list  = $this->mergeList($list2, $toDaylist);
        return $this->reSort($list);
    }



    public function getAllUsers(){

        $toDaylist = array();   
        if ($this->end >=  $this->today) {
            $departments = D('Department')->getSalesDepartments('id,name');
            // $groups = D('Group')->getAllGoups(array_column($departments,'id'), 'id, name, department_id');
            $departmentsMap = arr_to_map($departments, 'id');
            $toDaylist = $this->getTodayUsersAll();

            foreach ($toDaylist as $key => $value) {
                $toDaylist[$key]['department_name'] = $departmentsMap[$value['department_id']]['name'];
            }
        } 


        $list2 = M('statistics_usercustomers')->join("user_info as ui on statistics_usercustomers.user_id=ui.user_id")
                                                ->field($this->getSqlFields().", statistics_usercustomers.id,statistics_usercustomers.user_id, realname as name, statistics_usercustomers.department_id, statistics_usercustomers.department_name")
                                                
                                                ->where(array('date'=> array(array('EGT',$this->start),array('ELT',$this->end))))
                                                ->group('statistics_usercustomers.user_id')
                                                ->select();
        $list  = $this->mergeList($list2, $toDaylist);
        return $this->reSort($list);
    }

    public function getDepartmentAllUsers($department_id){

        $toDaylist = array();   
        if ($this->end >=  $this->today) {
            $groups = D('Group')->getAllGoups($department_id, 'id,name');
            $groupsMap = arr_to_map($groups, 'id');
            
            
            $toDaylist = $this->getDepartmentTodayUsersAll($department_id);        
            
            foreach ($toDaylist as $key => $value) {
                
                $toDaylist[$key]['g_name'] = $groupsMap[$value['group_id']]['name'];
            }
        } 
        




        $list2 = M('statistics_usercustomers')->field($this->getSqlFields().",statistics_usercustomers.user_id as id , gb.name as g_name,  ui.realname as name")
                                              ->join('group_basic as gb on statistics_usercustomers.group_id = gb.id ', 'left')
                                              ->join('user_info as ui on statistics_usercustomers.user_id = ui.user_id', 'left')
                                                ->where(
                                                    array(
                                                        'date'=> array(array('EGT',$this->start),array('ELT',$this->end)),
                                                        'statistics_usercustomers.department_id' =>$department_id
                                                        )
                                                    )
                                                ->group('statistics_usercustomers.user_id')
                                                ->select();
        // var_dump(M('statistics_usercustomers')->getlastsql());
        $list  = $this->mergeList($list2, $toDaylist);
        return $this->reSort($list);
    }
}