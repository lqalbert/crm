<?php 
namespace Upgrade\Controller;

use Think\Controller;

class SpreadController extends Controller{

    public function index(){
        $this->database();
        $this->setCustomer();
        $this->setAutoDistribute();
    }

    /**
    * 表更新字段
    */
    private function database(){
        $sql = " alter table `customers_basic` add column `spread_id` int unsigned not null default '0' comment '推广部id'; ";
        $sql .= " alter table `customers_basic` add column `depart_id` int unsigned not null default '0' comment '分配的销售部id';";
        $sql .= " alter table `customers_basic` add column `to_gid` int unsigned not null default '0' comment '分配的小组id'; ";
        $sql .= " alter table `customers_basic` add column `dis_time` TIMESTAMP   null default '' comment '分配时的时间'; ";
        $newTable = <<<TABLE
create table `distribute_basic`(
    `id` int unsigned not null primary key auto_increment ,
    `obj_id` int unsigned not null comment '单位id 部门id或是小组id',
    `type` tinyint unsigned not null comment '0总经办 1部门 2小组',
    `config` text comment 'json_encode序列化的数组',
    UNIQUE INDEX `depart_group_user` (`obj_id`,`type`)
)engine=innodb comment'自动分配参数表';

create table `distribute_record`(
    `id` int unsigned not null primary key auto_increment,
    `type` tinyint unsigned not null comment '类型 0 1 2',
    `obj_id` int unsigned not null ,
    `num` int unsigned not null default '0',
    `created_at` timestamp not null default  current_timestamp
)engine=innodb comment '分配记录表';

create table `distribute_detail`(
    `id` int unsigned not null primary key auto_increment,
    `record_id` int  not null,
    `name` varchar(10) ,
    `value` varchar(10)
)engine=innodb comment '详情表'


TABLE;




        M()->execute($sql);
        M()->execute($newTable);
    }


    public function setCustomer(){
        $sql = "alter table `customers_basic` add column `share_benefit` char(8)  null default ''";
        M()->execute($sql);
    }

    public function setAutoDistribute(){
        $m = M("distribute_basic");
        //obj_id 单位id 部门id或是小组id
        //type 0总经办 1部门 2小组
        //全都默认手动
        //1、生成总经办的
        
        $config = array();
        $config['limina'] = 0;
        $config['type'] = 1;
        $config['list'] = array();

        
        $data = array();
        $data['obj_id'] = 0;
        $data['type']   = 0;
        $data['config'] = json_encode($config);

        $m->create($data);
        $re = $m->add();
        echo $re;

        $departments = D("Home/Department")->getGoodSalesDepartments("id");
        
        foreach ($departments as $value) {
            $tmp = array();
            $tmp['obj_id'] = $value['id'];
            $tmp['type']   = 1;
            $tmp['config'] = json_encode($config);
            $m->create($tmp);
            echo $m->add();

            //小组
            $groups = D("Home/Group")->getAllGoups($value['id'], "id");

            foreach ($groups as $group) {
                $groupConfig = array();
                $groupConfig['obj_id'] = $group['id'];
                $groupConfig['type']   = 2;
                $groupConfig['config'] = json_encode($config);
                $m->create($groupConfig);
                echo $m->add();
            }


        }


    }
}