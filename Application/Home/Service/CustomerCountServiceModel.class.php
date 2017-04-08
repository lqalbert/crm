<?php
namespace Home\Model;
class CustomerCountServiceModel extends \Think\Model{

    /**
    * 时间
    */
    private $date = array('start'=>'', 'end'=>'');


    /**
    *  `type` 统计结果
    */
    private $typeCount = array();


    /**
    *  `V` 统计结果
    */
    private $VCount = array();

    /**
    *  `冲突` 别人 统计结果
    */
    private $conflictCount = array();

    /**
    *  `冲突` 被冲突 统计结果
    */
    private $conflictedCount = array();


    /**
    * 索取的 统计结果
    */
    private $pullCount = array();

    /**
    * 录入
    * 
    */
    private $createCount = array();

    /**
    * @param string '2017-01-01'
    * 
    * @return null;
    */
    public function setDate($date){
        $timestamp = strtotime($date);
        $this->date['start'] = date("Y-m-d H:i:s", strtotime('-1 second', $timestamp));
        $this->date['end']   = date("Y-m-d H:i:s", strtotime('+1 day', $timestamp));
    }


    /**
    * 生成 类型 统计结果
    */
    private function setTypeCount(){
        $sql = "select count(id) as c , `type` , salesman_id from customers_basic  group by `type`, salesman_id  ";

        $re = M()->query($sql);

        foreach ($re as  $value) {
            $this->typeCount[$value['type'].$value['salesman_id']] = $value['c'];
        }



        // 结果 存储：
        //   类型user_id  总数
        // [
        //     'A12'=> 12,
        // ]
    }

    private function setConflictCount(){
        $sql = "select count(id) as c, user_id from customers_conflict where created > ".$this->date['start']." and created <".$this->date['end']."  group by user_id";

        $re = M()->query($sql);

        foreach ($re as  $value) {
            $this->conflictCount[$value['user_id']] = $value['c'];
        }

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    private function setConflictedCount(){
        $sql = "select count(customers_conflict.id) as c , customers_basic.user_id from customers_basic inner join customers_conflict on customers_basic.id = customers_conflict.cus_id where customers_conflict.created > ".$this->date['start']." and customers_conflict.created <".$this->date['end']."  group by customers_basic.user_id";
        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->conflictedCount[$value['user_id']] = $value['c'];
        }

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    private function setPullCount(){
        $sql = "select count(cus_id)as c, to_id from customers_pull where created_at > ".$this->date['start']." and created_at <".$this->date['end']." group by to_id";
        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->pullCount[$value['to_id']] = $value['c'];
        }

        // 结果 存储：
        //   to_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    /**
    * 统计 指定时间 的成交量
    */
    private function setVCount(){
        $sql = "select count(cus_id), user_id from customers_service where time > ".$this->date['start']." and time <".$this->date['end']." group by user_id";

        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->VCount[$value['user_id']] = $value['c'];
        }
        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    private function setCreateCount(){
        $sql = "select count(id) as c  , user_id from customers_basic where created_at > ".$this->date['start']." and created_at <".$this->date['end']." group by user_id  ";

        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->createCount[$value['user_id']] = $value['c'];
        }

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }


    public function index($date){
        
        //业务逻辑层
        $this->setDate($date);

        // repository 层
        $this->setTypeCount();
        $this->setConflictCount();
        $this->setConflictedCount();
        $this->setPullCount();
        $this->setVCount();
        $this->setCreateCount();

        //业务逻辑层
        $this->setUserRecord();

        //存储数据
        /*[
            'user_id' ,'A', 'B'... 'conflict_to', 'conflict_from', 'pulls_num', 'create_num', 'all_num(总数，所有的类型加起来)', 'date(Y-m-d)'

        ]*/
    }


    private function setUserRecord(){
        $alluser= M()->query("select id from rbac_user where status>=0");
        $fields = array_merge(D('Customer')->getType(), array('conflict_to', 'conflict_from', 'pulls_num', 'create_num', 'all_num'));
        $re = array();
        foreach ($alluser as $value) {
            $tmp_row = array('user_id'=>$value['id']);
            foreach ($fields as $v2) {
                $tmp_row[$v2] = call_user_func(array($this, 'get'.$v2), $value['id']);
            }

            $re[] = $tmp_row;
        }

        // addAll;
    }

}

