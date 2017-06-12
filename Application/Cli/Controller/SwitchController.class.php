<?php
namespace Cli\Controller; 

class SwitchController extends \Think\Controller{




    
    public function index(){
        $this->sourceM = M('import_table4', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8'); //
        $re = $this->sourceM->query("select * from import_table4 where is_switch=1");

        foreach ($re as $key => $value) {
            var_dump($value);
            $ccRow = M('customers_contacts')->where(array('phone'=>$value['phone']))->field('cus_id')->find();

            $row = M('customers_basic')->field('id,user_id,salesman_id')->where(array("id"=>$ccRow['cus_id']))->find();

            M('customers_basic')->where(array('id'=>$row['id']))->data(array('user_id'=>$row['salesman_id'], 'salesman_id'=>$row['user_id']))->save();

            /*if (!$row) {
                $this->errorId[] = $value['id'];
            }*/
        }

        



    }
}