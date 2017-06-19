<?php
namespace Cli\Controller;

class FixServeTimeController extends \Think\Controller{

    public function index(){
        $this->deal();
    }

    public function deal(){
        $re = M('customers_basic')->where(array('service_time'=>0))->field('id,created_at')->select();
        var_dump($re);
        foreach ($re as $key => $value) {
            $sql="update customers_basic set service_time=".strtotime($value['created_at'])." where id=".$value['id'];
            $re = M()->execute($sql);
            echo $value['id'], " ", $re;
            echo "\n";
        }
    }
}