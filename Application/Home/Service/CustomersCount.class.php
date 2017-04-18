<?php
namespace Home\Service;

use Cli\Service\CustomerCountServiceModel;

class CustomersCount {

    private $date =   '';
    private $order = 'id asc';

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


    public function getDepartment(){
       


        
        $list = M('statistics_usercustomers')->field($this->getSqlFields().", id, department_name as name")
                                            ->where(array('date'=> $this->date))
                                            ->group('department_id')
                                            ->order($this->order)
                                            ->select();
        return $list;
    }

    public function getGroups($department_id = 0){
        
        return M('statistics_usercustomers')->field($this->getSqlFields().",id , group_name as name")
                                            ->where(array('date'=> $this->date, 'department_id'=>$department_id ))
                                            ->group('group_id')
                                            ->order($this->order)
                                            ->select();
    }


    public function getUsers($group_id){
        /*$sql = "select ".$this->getSqlFields(). ", group_name as name  from statistics_usercustomers where `date`='".$this->date."' and group_id=". $group_id ." group by user_id ";
        return M()->query($sql);*/
        return M('statistics_usercustomers')->join("user_info as ui on statistics_usercustomers.user_id=ui.user_id")
                                            ->field($this->getSqlFields().", statistics_usercustomers.id, realname as name")
                                            ->where(array('date'=> $this->date, 'statistics_usercustomers.group_id'=>$group_id ))
                                            ->group('statistics_usercustomers.user_id')
                                            ->order($this->order)
                                            ->select();
    }
}