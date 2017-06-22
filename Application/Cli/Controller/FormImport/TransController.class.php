<?php
namespace Cli\Controller;


class TransController extends \Think\Controller{

    private $all =0;

    private $users = array(
        "郭利飞"=>"龙换",
        "赵研状"=>"管宁",
        "李凯"=>"葛翠翠",
        "彭刘霞"=>"王帅",
        // "王静"=>"何有名",
        "薛晨光"=>"胡奎",
        "王新新"=>"王宁",//周口一部 超越队 348
        // "张豪"=>"李锦",
        "张超群"=>"葛翠翠",
        "李凯"=>"葛翠翠",
        "张诗杰"=>"信阳王猛",
    
        
    );


    public function index(){
        $this->sourceM = M('import_table4', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8'); //

        foreach ($this->users as $key=>$value) {
            echo $key, " ", $value; 
            echo "\n";
            $noRecords =$this->sourceM->where("ywy='".$key."'")->select();
            foreach ($noRecords as $val) {
                echo $val['phone'];
                $this->deal($val, $value);
            }
        }
        var_dump($this->all);
    }

    private function deal($record, $ywy){

        
        $sql = "select cb.id from customers_basic as cb inner join  customers_contacts as cc on cb.id=cc.cus_id and cc.is_main=1 where cc.phone='". $record['phone']."'";

        $row = M()->query($sql);
        if (!empty($row)) {
            $row = $row[0];
            $sale_id = M("user_info")->where("realname='".$ywy."'")->getField("user_id");
            $sql = "update customers_basic set old_sale_id=salesman_id where id=".$row['id'];
            M()->execute($sql);
            $sql = "update customers_basic set salesman_id=$sale_id   where id=".$row['id'];
            M()->execute($sql);
            $this->all++;
            
        } else {
            echo ' not found';
        }
        echo "\n";
    }
}