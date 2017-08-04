<?php 
namespace Upgrade\Controller;

use Think\Controller;

class SpreadController extends Controller{

    public function index(){
        $this->database();
    }

    /**
    * 表更新字段
    */
    private function database(){
        $sql = " alter table `customers_basic` add column `spread_id` int unsigned not null default '0' comment '推广部id'; ";
        $sql .= " alter table `customers_basic` add column `department_id` int unsigned not null default '0' comment '分配的销售部id';";
        $sql .= " alter table `customers_basic` add column `to_gid` int unsigned not null default '0' comment '分配的小组id'; ";
        $newTable = <<<TABLE
create table `distribute_basic`(
    `id` int unsigned not null primary key auto_increment ,
    `obj_id` int unsigned not null comment '单位id 部门id或是小组id',
    `type` tinyint unsigned not null comment '0总经办 1部门 2小组',
    `config` text comment 'json_encode序列化的数组',
    UNIQUE INDEX `depart_group_user` (`obj_id`,`type`)
)engine=innodb comment'自动分配参数表';
TABLE;
        M()->execute($sql);
        M()->execute($newTable);
    }
}