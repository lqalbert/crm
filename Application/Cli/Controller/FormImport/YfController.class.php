<?php 
namespace Cli\Controller ;

//9部也有一个杨
class YfController extends \Think\Controller {


    public function index(){

        $this->sourceM = M('import_table4', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8');
        $this->ccM     = M('customers_contacts');
        $this->cbM     = M('customers_basic');

        $sql = "SELECT customers_contacts.qq,customers_contacts.phone,customers_contacts.cus_id FROM customers_basic inner join customers_contacts on customers_basic.id=customers_contacts.cus_id WHERE salesman_id=237";

        $re = $this->ccM->query($sql);
        $ids = array();
        $tras  = array();
        foreach ($re as $key => $value) {
            $row = $this->sourceM->where(array('phone'=>$value['phone']))->field('id')->find();
            if ($row) {
                $ids[] = $row['id'];
            }else {
                //959
                $tras[] = $value['cus_id'];
                
            }
        }
        var_dump($tras);
        // $this->sourceM->execute("update import_table4 set is_yf=1 where id in(".implode(',', $ids).")");
        $aff_re =  $this->cbM->execute("update customers_basic set user_id=959 , salesman_id = 959 where id in(".implode(',', $tras).")");
        var_dump($aff_re);
    }
}