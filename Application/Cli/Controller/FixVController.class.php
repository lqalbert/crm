<?php
namespace Cli\Controller;

use Think\Controller;

class FixVController extends Controller {
    private $days = 0;
    private $dateTpl = '';

    public function index(){
        $this->init();
        $this->deal();
    }

    private function init(){
        $tmp = strtotime('2017-09-01');
        $this->days = Date('t', $tmp);
        $this->dateTpl = '2017-09-';
    }

    private function deal(){
        for ($i=1; $i <= $this->days ; $i++) { 
             $dateTpl = $this->dateTpl. sprintf("%02d", $i);
             echo $dateTpl;
             echo "\n";
             $record  = $this->getVRecord($dateTpl);
             // var_dump($record);
             if ($record) {
                 foreach ($record as $value) {
                    $row = M('statistics_usercustomers')->where(array('user_id'=>$value['user_id'], 'date'=>$dateTpl))
                                                         ->field('id,today_v')
                                                         ->find();
                    if ($row['today_v'] != $value['c']) {
                        $re = M('statistics_usercustomers')->data(array('today_v'=>$value['c']))
                                                           ->where(array('id'=>$row['id']))
                                                           ->save();
                        if ($re) {
                            echo 'id:', $row['id'], ' user_id:', $value['user_id'], ' c:', $value['c'];
                            echo "\n";
                        }
                    }
                 }
             }
        }
    }

    private function getVRecord($date){
        $startDate = $date.' 00:00:00';
        $endDate   = $date.' 23:59:59';
        $sql = "select count(cus_id) as c, salesman_id as user_id from customers_order where created_at >= '$startDate' and created_at <='$endDate' and salesman_id<>0  group by salesman_id";
        $re = M()->query($sql);
        return $re;
    }

}