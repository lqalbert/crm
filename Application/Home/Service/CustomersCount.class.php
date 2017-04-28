<?php
namespace Home\Service;

use Cli\Service\CustomerCountServiceModel;
use Home\Model\DepartmentCustomerStatisticsModel;
use Home\Model\GroupCustomerStatisticsModel;
use Home\Model\UserCustomerStatisticsModel;

class CustomersCount {

    private $date =   '';
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
        return $this;
    }

    public function setDate($date){
        $this->date = $date;
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
        $n->setOrder($this->order);
        return $n->index($this->date);
    }

    private function getTodayGroups($department_id){
        $d = new GroupCustomerStatisticsModel($department_id);
        $n = new CustomersCountToday($d);
        $n->setOrder($this->order);
        return $n->index($this->date);
    }

    private function getTodayUsers($group_id){
        $d = new UserCustomerStatisticsModel($group_id);
        $n = new CustomersCountToday($d);
        $n->setOrder($this->order);
        return $n->index($this->date);
    }


    public function getDepartment(){
        if ($this->date != $this->today) {
            $list = M('statistics_usercustomers')->field($this->getSqlFields().", department_id as id, department_name as name")
                                                        ->where(array('date'=> $this->date))
                                                        ->group('department_id')
                                                        ->order($this->order)
                                                        ->select();
        }  else {
            $list = $this->getTodayDepartment();
        }
        
        return $list;
    }

    public function getGroups($department_id = 0){
        if ($this->date != $this->today) {
            $list = M('statistics_usercustomers')->field($this->getSqlFields().",group_id as id , group_name as name")
                                            ->where(array('date'=> $this->date, 'department_id'=>$department_id ))
                                            ->group('group_id')
                                            ->order($this->order)
                                            ->select();
        }  else {
            $list = $this->getTodayGroups($department_id);
        }
        return $list;
    }


    public function getUsers($group_id){
        if ($this->date != $this->today) {
            $list = M('statistics_usercustomers')->join("user_info as ui on statistics_usercustomers.user_id=ui.user_id")
                                            ->field($this->getSqlFields().", statistics_usercustomers.id, realname as name")
                                            ->where(array('date'=> $this->date, 'statistics_usercustomers.group_id'=>$group_id ))
                                            ->group('statistics_usercustomers.user_id')
                                            ->order($this->order)
                                            ->select();
        }  else {
            $list = $this->getTodayUsers($group_id);
        }
        return $list;
    }
}