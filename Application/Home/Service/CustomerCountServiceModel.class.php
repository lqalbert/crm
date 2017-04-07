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

        // 结果 存储：
        //   类型user_id  总数
        // [
        //     'A12'=> 12,
        // ]
    }

    private function setConflictCount(){
        $sql = "select count(id) as c, user_id from customers_conflict where ".." group by user_id";

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    private function setConflictedCount(){
        $sql = "select count(customers_conflict.id) as c , customers_basic.user_id from customers_basic inner join customers_conflict on customers_basic.id = customers_conflict.cus_id where ".."  group by customers_basic.user_id";

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    private function setPullCount(){
        $sql = "select count(cus_id)as c, to_id from customers_pull where" .. " group by to_id";

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
        $sql = "select count(cus_id), user_id from customers_service where ".. " group by user_id";

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    private function setCreateCount(){
        $sql = "select count(id) as c  , user_id from customers_basic where ".." group by `type`, user_id  ";

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }


    public function index(){
        
        //存储数据
        /*[
            'user_id' ,'A', 'B'... 'conflict_to', 'conflict_from', 'pulls_num', 'create_num', 'all_num(总数，所有的类型加起来)', 'date(Y-m-d)'

        ]*/
    }

}

