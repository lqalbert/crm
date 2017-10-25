<?php
namespace Cli\Controller;

use Think\Controller;

class CheckOrderPhoneController extends Controller {

    public function index(){
        die();
        $this->orders = M("customers_order")->where(array('phone'=>array('NEQ','')))->field("id,cus_id,phone")->select();
        $re = array();
        foreach ($this->orders as $value) {
            $row = M("customers_contacts")->where(array('phone'=>$value['phone'], 'cus_id'=>$value['cus_id']))->find();
            if (!$row) {
                $row1 = M("customers_contacts")->where(array('cus_id'=>$value['cus_id'], 'is_main'=>1))->find();
                $value['cphone'] = $row1['phone'];
                // $re[] = $value;
                // $re[] = $value;
                M()->execute("update customers_order set phone='".$row1['phone']."' where id=".$value['id']);
            } 
        }

        foreach ($re as $key => $value) {
            var_dump($value);
        }
    }
}