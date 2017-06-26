<?php
namespace Home\Service;

use Cli\Service\CustomerCountServiceModel;
use Home\Model\DepartmentCustomerStatisticsModel;
use Home\Model\GroupCustomerStatisticsModel;
use Home\Model\UserCustomerStatisticsModel;
use Common\Lib\GetWeek;
use Common\Lib\GetMonth;

class CustomersTypeCount {

    private $date = '';
    private $start = '';
    private $end = '';
    private $order = 'id asc';

    private $today = '';


    public function __construct(){
        $this->today = Date('Y-m-d');
    }



    private function getFields(){
        return (new CustomerCountServiceModel)->getFields();
    }

    public function setOrder($order){
        $this->order = $order;

        $fields  = explode(' ', $this->order);
        $this->field = $fields[0];
        $sortMap = array('asc'=>SORT_ASC, 'desc'=>SORT_DESC );
        $this->sort = $sortMap[$fields[1]];
        

        return $this;
    }

    public function setDate($start,$end){
        $this->start = $start;
        $this->end = $end;
        return $this;
    }

    public function setRange($range){
        $this->range = $range;
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
        return $n->index($this->date);
    }

    private function getTodayGroups($department_id){
        $d = new GroupCustomerStatisticsModel($department_id);
        $n = new CustomersCountToday($d);
        // $n->setOrder($this->order);
        return $n->index($this->date);
    }

    private function getTodayUsers($group_id){
       
        if ($group_id!=0) {
            $d = new UserCustomerStatisticsModel($group_id);
            $n = new CustomersCountToday($d);
            
            return $n->index($this->date);
        } else {
            return array();
        }
        
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
        if ($this->range == 'day') {
            if ($this->end != $this->today) {
                $list = M('statistics_usercustomers')->field($this->getSqlFields().", department_id as id, department_name as name")
                                                            ->where(array('date'=> $this->date))
                                                            ->group('department_id')
                                                            // ->order($this->order)
                                                            ->select();

                $arrStart = M('statistics_usercustomers')->field($this->getSqlFields().", department_id as id, department_name as name")
                                                            ->where(array('date'=> $this->start))
                                                            ->group('department_id')
                                                            ->select();
               // sum(`all_num`) as all_num,sum(`own_num`) as own_num
                $OwnAllNumStart =  M('statistics_usercustomers')->field("sum(`all_num`) as all_num,sum(`own_num`) as own_num")
                                                           ->where(array('date'=> $this->start))->group('department_id')->select();
                                                           

                

            }  else {
                $list = $this->getTodayDepartment();
            }
        } else {
            switch ($this->range) {
                case 'week':
                    $week = new GetWeek($this->date);
                    $this->date = $week->getDay();
                    break;
                
                default:
                    $month = new GetMonth($this->date);
                    $this->date = $month->getDay();
                    break;
            }

            $list = M('statistics_usercustomers')->field($this->getSqlFields().", department_id as id, department_name as name")
                                                                ->where(array('date'=>array('in', $this->date)))
                                                                ->group('department_id')
                                                                // ->order($this->order)
                                                                ->select();
                                                                
            $list2 = $this->getTodayDepartment(); 
            $list = $this->mergeList($list, $list2);
        }
        return $this->reSort($list);
    }

    public function getGroups($department_id = 0){

        if ($this->range == 'day') {
            if ($this->date != $this->today) {
                $list = M('statistics_usercustomers')->field($this->getSqlFields().",group_id as id , group_name as name")
                                                ->where(array('date'=> $this->date, 'department_id'=>$department_id ))
                                                ->group('group_id')
                                                // ->order($this->order)
                                                ->select();
            }  else {
                $list = $this->getTodayGroups($department_id);
            }

        } else {

            switch ($this->range) {
                case 'week':
                    $week = new GetWeek($this->date);
                    $this->date = $week->getDay();
                    break;
                
                default:
                    $month = new GetMonth($this->date);
                    $this->date = $month->getDay();
                    break;
            }

            $list = M('statistics_usercustomers')->field($this->getSqlFields().", group_id as id , group_name as name")
                                                                ->where(array('date'=>array('in', $this->date), 'department_id'=>$department_id))
                                                                ->group('group_id')
                                                                // ->order($this->order)
                                                                ->select();
                                                                
            $list2 = $this->getTodayGroups($department_id); 
            $list = $this->mergeList($list, $list2);
        }
        return $this->reSort($list);
    }


    public function getUsers($group_id){

        if ($this->range == 'day') {

            if ($this->date != $this->today) {
                $list = M('statistics_usercustomers')->join("user_info as ui on statistics_usercustomers.user_id=ui.user_id")
                                                ->field($this->getSqlFields().", statistics_usercustomers.id, realname as name")
                                                ->where(array('date'=> $this->date, 'statistics_usercustomers.group_id'=>$group_id ))
                                                ->group('statistics_usercustomers.user_id')
                                                // ->order($this->order)
                                                ->select();
            }  else {

                $list = $this->getTodayUsers($group_id);
            }
        } else {
            
            switch ($this->range) {
                case 'week':
                    $week = new GetWeek($this->date);
                    $this->date = $week->getDay();
                    break;
                
                default:
                    $month = new GetMonth($this->date);
                    $this->date = $month->getDay();
                    break;
            }

            $list = M('statistics_usercustomers')->join("user_info as ui on statistics_usercustomers.user_id=ui.user_id")
                                                ->field($this->getSqlFields().", statistics_usercustomers.id, realname as name")
                                                ->where(array('date'=>array('in', $this->date), 'statistics_usercustomers.group_id'=>$group_id))
                                                ->group('statistics_usercustomers.user_id')
                                                // ->order($this->order)
                                                ->select();  
            $list2 = $this->getTodayUsers($group_id); 
            $list = $this->mergeList($list, $list2);
        }
        
        
        return $this->reSort($list);
    }
}