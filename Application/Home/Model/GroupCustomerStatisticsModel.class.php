<?php
namespace  Home\Model;

/**
* 部门客户统计
* 
*/
use Home\Model\DepartmentModel;

class GroupCustomerStatisticsModel extends CustomerCountModel {

    protected $autoCheckFields = false;


    private $depart_id=0;


    public function __construct($department_id){
        $this->depart_id = $department_id;
    }



    /**
    * 生成 类型 统计结果
    */
    public function setTypeCount(){
        $sql = "select count(cb.id) as c , `type` , ui.group_id from customers_basic as cb inner join user_info as ui on cb.salesman_id= ui.user_id where ui.department_id=".$this->depart_id." group by cb.type, ui.group_id";
        $re = M()->query($sql);

        foreach ($re as  $value) {
            $this->typeCount[$value['type'].$value['group_id']] = $value['c'];
        }
    }


     public function setConflictCount(){

        $sql = "select count(id) as c , ui.group_id from customers_conflict as cc inner join user_info as ui on cc.user_id = ui.user_id where cc.created > '".$this->date['start']."' and created < '".$this->date['end']."' and ui.department_id=".$this->depart_id." group by ui.group_id";

        $re = M()->query($sql);

        foreach ($re as  $value) {
            $this->conflictCount[$value['group_id']] = $value['c'];
        }

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    //设置被冲突
    public function setConflictedCount(){
        $sql = "select count(customers_conflict.id) as c , ui.group_id from customers_basic as cb inner join customers_conflict on cb.id = customers_conflict.cus_id inner join user_info as ui on cb.salesman_id = ui.user_id where customers_conflict.created > '".$this->date['start']."' and customers_conflict.created <'".$this->date['end']."' and ui.department_id=".$this->depart_id."  group by ui.group_id";
        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->conflictedCount[$value['group_id']] = $value['c'];
        }

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    public function setPullCount(){
        $sql = "select count(cus_id)as c, group_id from customers_pulls as cp inner join user_info as ui on cp.to_id = ui.user_id where created_at > '".$this->date['start']."' and created_at <'".$this->date['end']."' and ui.department_id=".$this->depart_id." group by group_id";
        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->pullCount[$value['group_id']] = $value['c'];
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
        $sql = "select count(cus_id), group_id from customers_service as cs inner join user_info as ui on cs.user_id = ui.user_id where time > '".$this->date['start']."' and time <'".$this->date['end']."' and ui.department_id=".$this->depart_id." group by group_id";

        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->VCount[$value['group_id']] = $value['c'];
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
        $sql = "select count(id) as c  , group_id from customers_basic as cb inner join user_info as ui on cb.user_id = ui.user_id where cb.created_at > '".$this->date['start']."' and cb.created_at <'".$this->date['end']."' and ui.department_id=".$this->depart_id." group by ui.group_id  ";

        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->createCount[$value['group_id']] = $value['c'];
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
        $sql = "select count(id) as c  , group_id from customers_basic as cb inner join user_info as ui on cb.user_id = ui.user_id where ui.department_id=".$this->depart_id."  group by ui.group_id  ";
        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->ownCount[$value['group_id']] = $value['c'];
        }
    }


    public function getTargets(){
        $re = D('Group')->getAllGoups($this->depart_id, 'id,name');

        return array_merge(array(array('id'=>0,'name'=>'其它')), $re);
    }

}