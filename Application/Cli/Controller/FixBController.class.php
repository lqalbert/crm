<?php 
namespace Cli\Controller;

class FixBController extends \Think\Controller{

    public function index(){
        $sql = "select id from customers_basic where user_id in (select user_id from user_info where department_id=6) and `type`='B'";


        $this->sourceM = M('import_table4', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8');

        $ids = $this->sourceM->query($sql);
        $type  = array('type'=>'B');
        foreach ($ids as $key => $value) {
            var_dump($value);
            M('customers_basic')->data($type)->where("id=".$value['id'])->save();
        }
    }
}