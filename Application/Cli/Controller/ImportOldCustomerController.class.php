<?php
namespace Cli\Controller;

use Think\Controller;

class ImportOldCustomerController  extends Controller{

    const SPREAD_TWO_ID = 24;

    private $departIds = array(67,68);
    private $mapDepartIds = array("67"=>24,"68"=>23);

    private $groupIds = array(
            24,13,14,15,16,18,20,22,23, // 67
            28,29,30,31,32,39,40,41   // 68
        );
    private $mapGroupIds = array(
        "24"=>"",
        "13"=>"",
        "14"=>"",
        "15"=>"",
        "16"=>"",
        "18"=>"",
        "20"=>"",
        "22"=>"",
        "23"=>"",
        "28"=>"",
        "29"=>"",
        "30"=>"",
        "31"=>"",
        "32"=>"",
        "39"=>"",
        "40"=>"",
        "41"=>"" 
    );


    private function initmapGroupIds(){
        foreach ($this->departIds as $value) {
            $groups = $this->sourceM->query("select * from group_basic where department_id=".$value);
            foreach ($groups as $group) {
                $ngroup = M('group_basic')->where(array("name"=>$group['name'], 'department_id'=>$this->mapDepartIds[$value]))
                                          ->field('id')
                                          ->find();
                $this->mapGroupIds[$group['id']] = $ngroup['id'];
            }
        }
    }

    public function index(){
        $this->sourceM = M('', null, 'mysql://run_crm_0326:run2008run@139.224.40.238:3306/run_crm_0326#utf8');
        $this->targetM = M();

        $this->initmapGroupIds();
        echo "init done";
        $this->repeate();
        echo 'done repeate';
        echo "\n";
        $this->deal(); 


        //给移过来的推广部的客户打标记
        $this->setSpreadCustomer(); 
        
    }

    //重复检测
    private function repeate(){
        $contacts = $this->sourceM->query("select * from customers_contacts ");
        foreach ($contacts as $value) {
            $qq     = $value['qq'];
            $phone  = $value['phone'];
            $weixin = $value['weixin'];
            var_dump($value);
            if (!empty($qq)) {
                if ($this->checkQQ($qq)) {
                    $this->confilict($value['cus_id'],1);
                    continue;
                }
            }

            if (!empty($phone)) {
                if ($this->checkPhone($phone)) {
                    $this->confilict($value['cus_id'],2);
                    continue;
                }
            }

            if (!empty($weixin)) {
                if ($this->checkWeixin($weixin)) {
                    $this->confilict($value['cus_id'],3);
                    continue;
                }
            }
        }
    }

    private function checkQQ($qq){
       $re =  M("customers_contacts")->where(array("qq"=>$qq))->find();
       if ($re) {
           return true;
       } else {
          return false;
       }
    }

    private function checkPhone($qq){
       $re =  M("customers_contacts")->where(array("phone"=>$qq))->find();
       if ($re) {
           return true;
       } else {
          return false;
       }
    }

    private function checkWeixin($qq){
       $re =  M("customers_contacts")->where(array("weixin"=>$qq))->find();
       if ($re) {
           return true;
       } else {
          return false;
       }
    }


    private function confilict($id, $err){
        // M("customers_basic")->data(array('is_has'=>$err))->where(array('id'=>$id))->save();
        $this->sourceM->execute("update customers_basic set is_has=$err where id=".$id);
    }


    private function deal(){
        $size = 100;
        $offset = 0;
        $users =  $this->sourceM->query("select * from customers_basic where is_has=0 limit $offset, $size");              // M("customers_basic")->where(array('is_has'=>0))->limit($offset, $size)->select();
        while ($users) {
            
            foreach ($users as $user) {
                $this->addCustomer($user);
            }

            $offset += $size;
            $users = $this->sourceM->query("select * from customers_basic where is_has=0 limit $offset, $size");
        }
    }

    private function addCustomer($user){
        $saleAccountId = M("user_info")->where(array('old_id'=>$user['salesman_id']))->getField("user_id");
        if (!$saleAccountId) {
            echo $data['name']." fail";
            echo "\n";
            return;
        }
        if ($user['user_id'] == $user['salesman_id'] ) {
            $userAccountId = $saleAccountId;
        } else {
            $userAccountId = M("user_info")->where(array('old_id'=>$user['user_id']))->getField("user_id");
        }
        $user['user_id']     = $userAccountId ;
        $user['salesman_id'] = $saleAccountId ;
        $oldId = $user['id'];
        unset($user['id']);
        $data = M("customers_basic")->create($user);
        echo $data['name'];
        if (!$data) {
            $this->setErro($oldId);
            echo ' fail';
            return;
        }

        $newId = M("customers_basic")->add();
        if (!$newId) {
            $this->setErro($oldId);
            echo ' fail';
            return;
        } 
        echo " success";
        echo "\n";

        $contacts = $this->sourceM->query("select * from customers_contacts where cus_id=".$oldId); 
           //M("customers_contacts")->where(array("cus_id"=>$oldId))
        foreach ($contacts as $contact) {
            unset($contact['id']);
            $contact['cus_id'] = $newId;
            try{
                M("customers_contacts")->create($contact);
                M("customers_contacts")->add();
            } catch (Exception $e){

            }
            
        }
    }

    private function setErro($id){
        $this->sourceM->execute("update customers_basic set is_has=4 where id=".$oldId);
    }


    private function setSpreadCustomer(){
        $users = M("user_info")->where(array('department_id'=>self::SPREAD_TWO_ID))->select();
        $this->mark = Date("Y-m-d");
        foreach ($users as $user) {
            $sql = "update customers_basic set spread_id=".$user['department_id'].",depart_id=".$user['department_id'].", to_gid=".$user['group_id'].", olde_mark='".$this->mark."' where salesman_id=".$user['user_id'];
            M()->execute($sql);
        }
    }


    public function conflict(){
        $this->sourceM = M('', null, 'mysql://run_crm_0326:run2008run@139.224.40.238:3306/run_crm_0326#utf8');
        $sql = "select id,user_id,salesman_id from customers_basic where is_has<>0";
        $customers = $this->sourceM->query($sql);
        foreach ($customers as $user) {
            
        }
        /*$ids = array(2, 19); // 9部是2 8部是19
        $users = M("user_info")->where(array("department_id"=>array('IN', $ids)))->field("user_id")->select();
        foreach ($users as $user) {
            $customers = M("customers_basic")->field("id")->where(array('user_id|salesman_id'=>$user['user_id']))->select();
        }*/

    }
}