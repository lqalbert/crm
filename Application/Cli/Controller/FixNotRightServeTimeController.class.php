<?php
namespace Cli\Controller;

class FixNotRightServeTimeController extends \Think\Controller{

    public function index(){
        $this->deal();
    }

    public function deal(){
        $sql = "select id,name, created_at from customers_basic where created_at <> FROM_UNIXTIME(service_time) and created_at < '2017-05-27 21:59:00' and id <>150886 order by id desc limit 1000";
        $re = M()->query($sql);

        while ($re) {
            foreach ($re as $key => $value) {
                $sql2="update customers_basic set service_time=".strtotime($value['created_at'])." where id=".$value['id'];
                $aff = M()->execute($sql2);
                echo $value['id'], " ", $aff;
                echo "\n";
            }

            $re = M()->query($sql);
        }
        
    }
}



