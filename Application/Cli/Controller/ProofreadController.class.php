<?php
namespace Cli\Controller;
// 校对

class ProofreadController extends \Think\Controller{

    private $users = array(
        "曹京华"=>"李蓝天",
        "李文静"=>"罗文",
        "李文博"=>"贾龙",
        "李文浩"=>"贾龙",
        "李灿"=>"贾龙",
        "涂团结"=>"贾龙",
        "齐刘红"=>"贾龙",
        "朱坤磊"=>"任丙月",
        "孙永霞"=>"马亚光",
        "李鹏辉"=>"马亚光",
        "王俐"=>"马亚光",
        "胡文文"=>"马亚光",
        "赵广飞"=>"马亚光",
        "师俊男"=>"杨旗",
        "李鹏锁"=>"杨旗",
        "王丹萍"=>"朱梦娟",
        "王叶房"=>"王林静",
        "左和民"=>"谢兵",
        "赵飞"=>"王宁",
        "袁二杰"=>"吕超杰",
        "张泽"=>"韦拾东",
        "岳笑笑"=>"杨成涛",
        "张国旗"=>"杨成涛",
        "孟祥真"=>"彭学旺",
        "叶忠瓒"=>"王燕",
        "岳帅峰"=>"王燕",
        "马帅祺"=>"王燕",
        "张夫山"=>"王宁",
        "杨培杰"=>"李锦",
        "杨露"=>"李锦",
        "陈良杰"=>"李锦",
        "孟凡琳"=>"王亚茹",
        "房勇"=>"王亚茹",
        "芦亚民"=>"李京",
        "苏明"=>"3部张帅",
        "李玉龙"=>"韦拾东",
        "李虹"=>"韦拾东",
        "田红莉"=>"吕超杰",
        "马邵伟"=>"吕超杰",
        "李浩玮"=>"张阿丹",
        "张淑平"=>"张阿丹",
        
    );


    public function index(){
        $this->sourceM = M('import_table4'); //, null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8'

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
        $sql = "select ui1.realname as cjr, ui2.realname as ywy from customers_basic as cb inner join  customers_contacts as cc on cb.id=cc.cus_id and cc.is_main=1 inner join user_info as ui1 on cb.user_id=ui1.user_id inner join user_info as ui2 on cb.salesman_id = ui2.user_id where cc.phone='". $record['phone']."'";

        $row = M()->query($sql);
        if (!empty($row)) {
            $row = $row[0];
            if ($row['ywy'] != $ywy ) {
                echo " no_correct";
                $this->sourceM->execute("update import_table4 set is_correct=1 where id =".$record['id']);
            } else {
                echo " correct";
            }
        } else {
            echo ' not found';
        }
        echo "\n";
    }
}