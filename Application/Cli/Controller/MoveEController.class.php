<?php
namespace Cli\Controller;

use Think\Controller;

class MoveEController extends Controller {

    const DEPART = 25;

    private $list = array(
        "魏鹏举",
        "彭永飞",
        "李楠",
        "徐洋",
        "蔡俊伟",
        "尹丽丽",
        "刘辉",
        "袁凡",
        "秦垭男",
        "秦艳香",
        "张珂",
        "赵建超",
        "赵世豪",
        "朱颖颖"
    );




    public function index(){
        $ids = $this->cheIDs();
        // $ids = array();
        $sql = "update user_info set department_id=25,group_id=0 where user_id in (". implode(",", $ids) .")";
        M()->execute($sql);
    }

    private function cheIDs(){
        $ids = array();
        foreach ($this->list as $value) {
           $re =  M("user_info")->where(array("realname"=>$value))->select();
           if (empty($re)) {
               echo $value." ";
           }
           if (count($re)==1) {
               $ids[] = $re[0]['user_id'];
           } else {
             echo $value." ";
           }
        }
        return $ids;

        if (count($this->list) == count($ids)) {
            echo 'yes';
        } else {
            echo 'no';
        }
    }

    public function fixUser(){

        $arr = array(
            
            "朱鹏",
            "张金东",
            "石磊",
            "张家维",
            "吴攀哲",
            "陈润红",
            "谷文峰" 
        );
        /*
            
        */

        $str = array();
        foreach ($arr as $key => $value) {
            $str[] = "realname='{$value}'";
        }

        $sql="update rbac_user set status=1 where id in (select user_id from user_info where  department_id=3 and ( ". implode(" or ", $str) ." ))";

        M()->execute($sql);
    }
}




