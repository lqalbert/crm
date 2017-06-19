<?php
namespace Cli\Controller;

class CheckSuMengYuanController extends \Think\Controller {

    public function index(){
        $this->sourceM = M('import_table_fixtime3', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8');
        
        // $this->sourceM = M('import_table_d7');
        $this->ccM     = M('customers_contacts');
        $this->cbM     = M('customers_basic');
        $this->uiM     = M('user_info');

        $this->deal();
    }


    public function deal(){
        $re = $this->sourceM->where(array('ywy'=>'苏梦圆'))->select();
        foreach ($re as $key => $value) {
            $phone = $value['phone'];
            $ccRow = $this->ccM->where(array('phone'=>$phone))->find();
            if ($ccRow) {
                $cbRow = $this->cbM->where(array('id'=>$ccRow['cus_id']))->find();
                $sql="update import_table_fixtime3 set group_user=".$cbRow['salesman_id']." where id=".$value['id'];
                $this->sourceM->execute($sql);

            }

        }
    }
}