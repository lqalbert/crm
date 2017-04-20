<?php
namespace  Cli\Service;

use Common\Model\StatisticsGroupModel;



class GroupCountServiceModel extends \Think\Model {

    /**
    * 存储层
    */
    private $groupModel = null;
    private $userModel  = null;
    private $StUserModel   = null;
    private $StGroupModel = null;


    private $date = '';

    /**
    * 成交多少个
    */
    private vCount = array(); 

    /**
    * 员工多少个
    */
    private eCount = array();

    /**
    * 录入多少个
    */
    private aCount = array();

    /**
    * 所有的小组
    */
    private groups = array();

    public function init(){
        $this->StGroupModel = new StatisticsGroupModel;
        $this->userModel    = D('\Home\Model\UserModel');
        $this->StUserModel  = M('statistics_usercustomers');
        $this->groupModel   = D('\Home\Model\GroupModel');
    }

    public function index($date){
        $this->init();
        $this->date = $date;

        
        $this->setGroups();

        $this->setVCount();
        $this->setECount();
        $this->setACount();



        $this->run();

    }


    private function setVCount(){
        $sql = "select sum(today_v) as c, group_id from statistics_usercustomers where `date`='". $this->date ."' group by group_id";
        $this->vCount  =  arr_to_map( M()->query($sql), 'group_id', 'c' );
    }

    private function setECount(){
        foreach ($this->groups as $key => $value) {
            $this->eCount[$key] = count($this->userModel->getGroupEmployee($value, 'id')) ;
        }
    }

    private function setACount(){
        $sql = "select sum(create_num) as c, group_id from statistics_usercustomers where `date`='". $this->date ."' group by group_id";
        $this->aCount  =  arr_to_map( M()->query($sql), 'group_id', 'c' );
    }

    private function setGroups(){
        $sql = "select gb.id,gb.name , db.name as department_name from group_basic as gb inner join department_basic as db on gb.department_id = db.id";
        $this->gorups = arr_to_map(M()->query($sql), 'id');
    }

    private function getVnum($id){
        if (isset($this->vCount($id))) {
            return $this->vCount($id);
        } else {
            return 0;
        }
    }

    private function getEnum($id){
        if (isset($this->eCount($id))) {
            return $this->eCount($id);
        } else {
            return 0;
        }
    }

    private function getAnum($id){
        if (isset($this->aCount($id))) {
            return $this->aCount($id);
        } else {
            return 0;
        }
    }

    


    private function run(){
        $re = array();
        foreach ($this->groups as $key => $value) {
            $tmp = array(
                'group_id' =>$key,
                'group_name' =>$value['name'],
                'department_name' => $value['department_name'],
                'date' => $this->date;
                'v_num' = $this->getVnum($key);
                'employee_num' = $this->getEnum($key);
                'add_num' = $this->getAnum($key);
            )
            $re[] = $tmp;
        }

        return $this->StGroupModel->addAll($re);
    }
}