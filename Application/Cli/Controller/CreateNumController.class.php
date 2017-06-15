<?php
namespace Cli\Controller;


/**
* 生成历史锁定数
* 生成2017年 1月 到 5月的锁定数
* 5月份应到27号为止
*
*/
class CreateNumController extends \Think\Controller {

    private $year = "2017";
    private $months = array("01","02","03","04","05");
    private $monthDay = array();
    private $allRe = array();
    private $allUsers = array();
    private $insert_data = array();


    public function index(){

        $this->dealDate();
        $this->setCount();
        $this->setUserGroup();
        $this->setInsert();


        
        
    }


    private function dealDate(){
        foreach ($this->months as $key => $value) {
            $date = $this->year."-".$value."-01";
            $this->monthDay[$value]= date("t", strtotime($date));
        }
    }

    private function setCount(){
        foreach ($this->monthDay as $month => $days) {
            for ($i=1; $i < $days; $i++) { 
                $date = $this->year."-".$month."-".sprintf("%02d", $i);
                $start = $date." 00:00:00";
                $end   = date("Y-m-d H:i:s", strtotime($start) + 86400) ;
                $this->getCount($date, $start, $end);
            }
        }
    }



    private function getCount($date, $start, $end){
         $sql = "select count(id) as c  , user_id from customers_basic where created_at > '".$start."' and created_at <'".$end."' group by user_id  ";
         $re  = M()->query($sql);
         $tmp = array();
         // user_id => c;
         foreach ($re as $key => $value) {
             $tmp[$value['user_id']] = $value['c'];
         }
         $this->allRe[$date] = $tmp;
    }

    private function setUserGroup(){
        $sql = "select ui.user_id,ui.group_id, ui.department_id, gb.name as group_name, db.name as department_name from user_info as ui left join group_basic as gb on ui.group_id=gb.id left join department_basic as db on ui.department_id = db.id ";
        $allUsers = M()->query($sql);
        foreach ($allUsers as $key => $value) {
            $this->allUsers[$value['user_id']] = $value;
        }
    }

    private function setInsert(){
        foreach ($this->allRe as $date => $re) {
            var_dump($date);
            var_dump(count($re));
            foreach ($re as $user_id => $count) {
                if (isset($this->allUsers[$user_id])) {
                    $tmp = array('date'=>$date);
                    $tmp['user_id'] = $user_id;
                    $tmp['group_id']        = $this->allUsers[$user_id]['group_id'];
                    $tmp['department_id']   = $this->allUsers[$user_id]['department_id'];
                    $tmp['group_name']      = $this->allUsers[$user_id]['group_name'];
                    $tmp['department_name'] = $this->allUsers[$user_id]['department_name'];
                    $tmp['create_num'] = $count;
                    $this->insert_data[] = $tmp;
                }
            }

            if (count($this->insert_data) > 500) {
                $this->save();
            }
        }
    }


    private function save(){
        $re = M('statistics_usercustomers')->addAll($this->insert_data);
        echo $re;
        echo "\n";
        $this->insert_data = array();
    }



} 