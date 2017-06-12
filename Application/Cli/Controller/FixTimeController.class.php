<?php
namespace Cli\Controller;


class FixTimeController extends \Think\Controller {


    public function index(){
        $sql = "SELECT id FROM `customers_basic` WHERE  created_at >= '2017-05-27 21:56:27' and created_at < '2017-05-28 00:00:00'";

        $this->sourceM = M('import_table_fixtime', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8'); //
        $re = M()->query($sql);
        foreach ($re as $value) {
            echo "id:",$value['id'];
            echo "\n";
            $ccRow=M("customers_contacts")->where(array('cus_id'=>$value['id']))->field('qq,phone')->find();
            // var_dump(M("customers_contacts")->getlastsql());
            // var_dump($ccRow);

            $record = $this->sourceM->where(array('qq'=>$ccRow['qq'], 'phone'=>array('like','%'.$value['phone'].'%')))->find();
            // var_dump($this->sourceM->getlastsql());
            // var_dump($record);
            // die();
            // var_dump($this->sourceM->getlastsql());
            if ($record) {
                $cbre = M('customers_basic')->data(array('created_at'=>$record['create_at']))->where(array("id"=>$value['id']))->save();
                var_dump($cbre);
            }
        }
    }


    /*public function index(){
        $root = getcwd();
        $done = $root ."\\"."datafix";

        $updar = scandir($done);

        foreach ($updar as $value) {
             $subdir = $done."\\".$value;
            if ($value =="." ||  $value==".." ) {
                continue;
            }

            
            // var_dump($value);
            
            $tmp = pathinfo($subdir, PATHINFO_EXTENSION);
            if ($tmp != 'xls') {
               return ;
            }
            $data = getExcelArrayData($subdir);
            foreach ($data as $item) {
                var_dump($item);
                $row = M('import_table4')->where(array('encode'=>$item['K']))->field('id')->find();
                if ($row) {
                    M('customers_basic')->data(array('created_at'=>$item['H']))->where(array('id'=>$row['id']))->save();
                    echo $item['K'];
                    echo "\n";
                    exit();
                }
                
            }
        }
    }*/
}