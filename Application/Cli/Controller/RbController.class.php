<?php 
namespace Cli\Controller;

class RbController extends \Think\Controller{

    public function index(){
        $re = M('customers_pulls')->where(array('created_at'=>array('gt', '2017-06-19 00:00:00')))->order('from_id')->select();
        foreach ($re as $key => $value) {
            $c = M('customers_basic')->find($value['cus_id']);
            echo $c['name'];
            $u = M('rbac_user')->find($value['from_id']);
            echo " 员工：".$u['account'];
            echo ' 还原';
            echo "\n";
            /*echo $value['cus_id'];
            echo "\n";*/
            // $sql = "update customers_basic set salesman_id = ".$value['from_id']. " where id=".$value['cus_id'];
            // M()->execute($sql);
        }
    }
}