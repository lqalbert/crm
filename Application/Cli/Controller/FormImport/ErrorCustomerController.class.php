<?php
namespace Cli\Controller; 

class ErrorCustomerController extends \Think\Controller{



    private $user = array(
        '李向前'=>967,
    );

    private $errorId = array();
    public function index(){

        $re = M()->query("select customers_basic.id,user_id,salesman_id,name,`type`, created_at ,customers_contacts.phone,customers_contacts.qq  from customers_basic inner join customers_contacts  on  customers_basic.id=customers_contacts.cus_id where salesman_id=967 ");

        foreach ($re as $key => $value) {
            var_dump($value);
            $row = M('import_tabledepartment')->where(array('phone'=>$value['phone']))->find();
            if (!$row) {
                $this->errorId[] = $value['id'];
            }
        }

        M('customers_basic')->where(array('id'=>array('in',$this->errorId)))->data(array('is_error'=>1))->save();



    }
}