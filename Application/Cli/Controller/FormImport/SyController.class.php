<?php 
namespace Cli\Controller ;

class SyController extends \Think\Controller {


    private $users = array(
        "296"=>"杨杰",
        "295"=>"黄艳华"
    );

    public function index(){

        $this->sourceM = M('import_table4', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8'); //

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
                        $is_wrong[] = $record['id'];
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

            if (!empty($is_deal)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set is_deal=1 where id in(". implode(",", $is_deal).")");
                $is_deal = array();
            }

            if (!empty($no_record)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set no_record=1 where id in(". implode(",", $no_record).")");
                $no_record = array();
            }

            if (!empty($is_wrong)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set is_wrong=1 where id in(". implode(",", $is_wrong).")");
                $is_wrong = array();
            }


        }




    }
}