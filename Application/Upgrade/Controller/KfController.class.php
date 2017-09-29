<?php
namespace Upgrade\Controller;

class KfController  extends \Think\Controller{

    public function index(){

    }

    public function table(){
        $sql ="create table `msg_alert`(
                `id` int unsigned not null primary key auto_increment,
                `to_id` int unsigned not null comment '员工id',
                `title` varchar(50) comment '消息标题',
                `content` text comment '消息内容',
                `is_send` tinyint unsigned not null default '0' comment '是否发送过',
                `created_at` timestamp not null default  CURRENT_TIMESTAMP 
            ) comment '给风控回访材料客服经理等的弹窗';";

        M()->execute($sql);

        $sql = "create table `customers_complain`(
                `id` int unsigned  not null primary key auto_increment,
                `cus_id` int unsigned not null comment '客户id',
                `user_id` int unsigned not null comment '操作的员工id',
                `type` tinyint unsigned not null default '0',
                `content` varchar(255) null,
                `created_at` timestamp not null default CURRENT_TIMESTAMP
            )comment '客户投诉';";
        M()->execute($sql);

        $sql = "alter table `customers_buy` add column `created_at` timestamp not null default CURRENT_TIMESTAMP;";
        M()->execute($sql);
    }


    //角色的修改
    public function role(){
        //1、添加 中间一层 “部门经理” Master 
        //  这一层就是所有的“经理”的父角色 这一层角色能承担通用的职能
        $rId = M("rbac_role")->add(
            array('name'=>"经理",
                  'ename'=>'master',
                  'pid'=>0,
                  'status'=>1,
                  'remark'=>'管部门')
        );
        $cId = M("rbac_role")->add(
            array('name'=>"投顾经理",
                  'ename'=>'counselorMaster',
                  'pid'=>$rId,
                  'status'=>1,
                  'remark'=>'管部门')
        );
        //原角色 departmentMaster、serviceMaster、riskMaster 的父级角色改成这个
        /*M("rbac_role")->data(array('pid'=>24))->where(
            array('ename'=>array(array('departmentMaster'),array('serviceMaster'),array('riskMaster'), 'or'))
        )->save();*/
        $sql = "update rbac_role set pid=$rId where ename='departmentMaster' or ename='serviceMaster' or ename='riskMaster' ";
        M()->execute($sql);


    }
}