<?php
namespace Cli\Controller;

use Think\Controller;

class FixVDisController extends Controller {


    public function index(){
        $record = $this->getRecords();
        foreach ($record as $key => $value) {
            $sql  = "update customers_basic set salesman_id=0, depart_id=0,to_gid=0 , olde_mark='2017-10-11' where id=".$value['id'];
            echo "id:", $value['id'], " - ",M()->execute($sql);
            echo "\n";
        }
    }


    private function getRecords(){
        $sql = "SELECT id FROM `customers_basic` where dis_time>'2017-10-09' and created_at<'2017-10-09' and type='V' and spread_id<>0 and depart_id<>0 order by created_at";
        return M()->query($sql);
    }
}