<?php
namespace  Home\Model;

/**
* 部门客户统计
* 
*/
use Home\Model\DepartmentModel;

class UserCustomerStatisticsAllModel extends CustomerCountModel {

    protected $autoCheckFields = false;



    /**
    * 生成 类型 统计结果
    */
    public function setTypeCount(){
        /*$sql = "select count(cb.id) as c , `type` , ui.user_id from customers_basic as cb inner join user_info as ui on cb.salesman_id= ui.user_id where    cb.status=1 group by cb.type, ui.user_id";
        
        $re = M()->query($sql);

        foreach ($re as  $value) {
            $this->typeCount[$value['type'].$value['user_id']] = $value['c'];
        }*/

    }


     public function setConflictCount(){

        $sql = "select count(id) as c , ui.user_id  from customers_conflict as cc inner join user_info as ui on cc.user_id = ui.user_id where cc.created > '".$this->date['start']."' and created < '".$this->date['end']."'  group by ui.user_id ";

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

    //设置被冲突
    public function setConflictedCount(){
        $sql = "select count(customers_conflict.id) as c , ui.user_id  from customers_basic as cb inner join customers_conflict on cb.id = customers_conflict.cus_id inner join user_info as ui on cb.salesman_id = ui.user_id where customers_conflict.created > '".$this->date['start']."' and customers_conflict.created <'".$this->date['end']."'   group by ui.user_id ";
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

    public function setPullCount(){
        $sql = "select count(cus_id)as c, ui.user_id from customers_pulls as cp inner join user_info as ui on cp.to_id = ui.user_id where created_at > '".$this->date['start']."' and created_at <'".$this->date['end']."' group by ui.user_id";
        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->pullCount[$value['user_id']] = $value['c'];
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
    public function setVCount(){
        $sql = "select count(cus_id) as c, ui.user_id from customers_order as cs inner join user_info as ui on cs.user_id = ui.user_id where created_at > '".$this->date['start']."' and created_at <'".$this->date['end']."' group by ui.user_id";

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


    /**
    * 当日 添加的客户
    */
    public function setCreateCount(){
        $sql = "select count(id) as c  , ui.user_id from customers_basic as cb inner join user_info as ui on cb.user_id = ui.user_id where cb.created_at > '".$this->date['start']."' and cb.created_at <'".$this->date['end']."' group by ui.user_id   ";

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

    /**
    * 自锁客户 总数
    */
    public function setOwnCount(){
        $sql = "select count(id) as c  , ui.user_id from customers_basic as cb inner join user_info as ui on cb.user_id = ui.user_id   group by ui.user_id   ";
        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->ownCount[$value['user_id']] = $value['c'];
        }
    }

    public function getTargets(){
        return D('User')->getHasCustomerEmployee('id,realname as name, department_id');

    }

}