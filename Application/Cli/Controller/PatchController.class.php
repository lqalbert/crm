<?php
namespace Cli\Controller;

use Home\Model\CustomerContactModel;

class PatchController extends \Think\Controller{



    private $users = array(
        "邵彦洁",
        "裴云祥",
        "崔旭阳",
        "江国贺",
        "徐喜贵",
        "姚凤云",
        "张浩瀚",
        "姬林果",
        "彭功轩",
        "武海峰",
        "于洋",
        "孙雪奎",
        "张文军",
        "路帅",
        "胡保胜",
        "孟秀珍",
        "闫勇占",
        "王福朝",
        "刘叶",
        "吴亚洲",
        "王晓波",
        "庄晓培",
        "谢旭东",
        "马好蕊",
        "3部王玉林",
        "尚金锁",
        "苏梦圆",
        "武倩倩",
        "王浩林",
        "李妮",
        "张秋杰",
        "杨帅",
        "王艳艳",
        "白龙飞",
        "吴飞飞",
        "刘昶彤",
        "许景豫",
        "郭珮",
        "陈冉",
        "韩伟阵",
        "宋仪伟",
        "苏明",
        "吕向阳",
        "王梦赛",
        "何龙浩",
        "杨梦梦",
        "牛淼艳",
        "郭素平",
        "李凯",
    );

    private $all = 0;
    public function index(){

        $this->sourceM = M('import_table4_no_record', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8'); //

        foreach ($this->users as $value) {
            $noRecords =$this->sourceM->where("ywy='".$value."'")->select();
            foreach ($noRecords as $key => $value) {
                $this->isExsits($value);
            }
        }
        var_dump($this->all);
    }

    private function isExsits($record){
        /*$row = M('user_info')->where(array('realname'=>$record['ywy']))->find();
        if ($row) {
            $this->insert($record, $row['user_id']);
        } else {
            //导入到小组主管头上
            
        }*/

        $this->insert($record, $record['group_user']);
    }




    private function insert($value, $user_id){
        $m = M();
        $basicData = array(
            'name'=> $value['name'],
            'type'=> strtoupper(mb_substr($value['ctype'], 0,1)) ,
            'area_province'=>null,
            'area_city'=>null,
            'user_id'=>$user_id,
            'salesman_id'=>$user_id,
            'oldencode'   => $value['encode'],
            'created_at'   => $value['create_at'],
        );

        
        

        $contactData = array(
            'cus_id'  =>  0,
            'phone'   =>  $value['phone'],
            'qq'      =>  empty($value['qq']) ? null : $value['qq'],
            'is_main' => 1
        );
        $cc = new CustomerContactModel();
        $re = $cc->create($contactData);
        
        if($re){
            $basicData =  M('customers_basic')->create($basicData);
            /*if (!$basicData) {
                 echo $value['name'], "fail";
                 echo "\n";
            }*/
            $m->startTrans();
            $cus_id = M('customers_basic')->add();
            
            if (!$cus_id) {
                $m->rollback();
                // $this->error("Customer".  M('customers_basic')->getError());
                echo $value['encode'], "fail.customers_basic";
                echo "\n";
            } else {
                $re['cus_id'] = $cus_id;
                $id = $cc->data($re)->add();
                if (!$id) {
                    $m->rollback();
                    echo $value['encode'], "fail.cc";
                    echo "\n";
                    // $this->error(D('CustomerContact')->getError());
                }  else {
                    $m->commit();
                    $this->all++ ;
                    echo $value['encode'], "success";
                    echo "\n";
                    $this->sourceM->execute("update import_table4_no_record set no_record=0 where id=".$value['id']);
                    // M('import_table2')->where(array('id'=>$value['id']))->delete();
                }
            }
        } else {
            
            echo $value['qq'];
            echo  iconv('UTF-8','EUC-CN', $cc->getError()); 
            echo "\n";
            $this->sourceM->execute("update import_table4_no_record set is_fail=1 where id=".$value['id']);
        }
        $cc = null;
    }
}