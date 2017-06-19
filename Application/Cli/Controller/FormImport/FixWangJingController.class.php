<?php 
namespace Cli\Controller ;

class FixWangJingController extends \Think\Controller {


    private $users = array(
        "249"=>"王静",
    );

    public function index(){

        $this->sourceM = M('import_table_fixtime3', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8'); //
        // $this->sourceM = M('import_table_fixtime3', null, 'mysql://dev_crm_02:dev2008dev@192.168.0.12/dev_crm_02#utf8'); //

        $this->ccM     = M('customers_contacts');
        $this->cbM     = M('customers_basic');
        $is_deal = array();
        $no_record = array();
        $is_wrong = array();
        foreach ($this->users as $keyId=> $value) {
            echo $value;
            $allRecords = $this->sourceM->where(array('ywy'=>$value))->select();
            foreach ($allRecords as $record) {
                var_dump($record['phone']);
                $row = $this->ccM->where(array('phone'=>$record['phone']))->find();
                if ($row) {
                    $is_deal[] = $record['id'];
                    $cusRow = $this->cbM->field("salesman_id")->find($row['cus_id']);
                    if ($cusRow['salesman_id']!=$keyId) {
                        $is_wrong[] = $row['cus_id'];
                    }
                } else {
                    $no_record[] = $record['id'];
                }
            }
            echo 'is_deal:', count($is_deal);
            echo "\n";
            echo 'no_record:', count($no_record);
            echo "\n";
            echo 'is_wrong:', count($is_wrong);
            echo "\n";


           /* $this->sourceM->execute("update import_table4 set is_deal=1 where id in(". implode(",", array_column($allRecords, 'id')).")");*/

           $sql= "update customers_basic set user_id=$keyId, salesman_id=$keyId  where id in (". implode(',', $is_wrong).")";
           $this->cbM->execute($sql); 

        }




    }
}