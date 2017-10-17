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

    public function table2(){
        $sql ="CREATE TABLE `statistics_spread_achievement` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id自增',
                `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户id',
                `group_id` INT(10) UNSIGNED NOT NULL COMMENT '小组id',
                `group_name` VARCHAR(20) NULL DEFAULT NULL COMMENT '小组名称',
                `department_id` INT(10) UNSIGNED NOT NULL COMMENT '部门id',
                `department_name` VARCHAR(20) NULL DEFAULT NULL COMMENT '部门名称',
                `date` CHAR(10) NULL DEFAULT NULL COMMENT '时间',
                `order_num` MEDIUMINT(9) NOT NULL DEFAULT '0' COMMENT '当日订单个数',
                `sale_amount` MEDIUMINT(9) NOT NULL DEFAULT '0' COMMENT '当日销售金额',
                `upgrade_num` MEDIUMINT(9) NOT NULL DEFAULT '0',
                `upgrade_amount` MEDIUMINT(9) NOT NULL DEFAULT '0',
                `renew_num` MEDIUMINT(9) NOT NULL DEFAULT '0',
                `renuew_amount` MEDIUMINT(9) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;";
        M()->execute($sql);
    }

    public function table3(){
        die('done');
        $sql = "alter table `customers_order` add column source_type tinyint unsigned not null default '1' comment '1 自锁 2推广';";
        M()->execute($sql);
    }

    public function fixSourceType(){
        die('done');
        $re = M('customers_order')->where(array('user_id'=>array('NEQ',0)))->select();
        foreach ($re as $order) {
            $spId = D("Home/Customer")->where(array('id'=>$order['cus_id']))->getField('spread_id');
            if ( $spId != 0 ) {
                $sql =" update customers_order set source_type=2 where id=".$order['id'];
                echo $order['id']," ",M()->execute($sql);
            }  
        }
    }

    public function fixAchiev(){
        die('done');
        $re = M('customers_order')->where(array('user_id'=>array('NEQ',0)))->select();
        foreach ($re as $order) {
            $tmp = explode(" ", $order['created_at']);
            var_dump($order);
            if ($order['source_type'] == 1) {
                $sql = " update statistics_sale_achievement set self_num=self_num+1 ,self_amount=self_amount+".intval($order['paid_in'])." where `date`= '".$tmp[0]."' and user_id= ".$order['salesman_id'];
            } else {
                $sql = " update statistics_sale_achievement set spread_num=spread_num+1 ,spread_amount=spread_amount+".intval($order['paid_in'])." where `date`= '".$tmp[0]."' and user_id=".$order['salesman_id'];
            }
            M()->execute($sql);
            
        }
    }


    public function table4(){
        die('done');
        $sql = "alter table `statistics_sale_achievement` add column self_num mediumint  not null default '0' comment '自锁成交数';".
               "alter table `statistics_sale_achievement` add column self_amount mediumint not null default '0' comment '自锁成交金额';".
               "alter table `statistics_sale_achievement` add column spread_num mediumint not null default '0' comment '推广成交数';".
               "alter table `statistics_sale_achievement` add column spread_amount mediumint not null default '0' comment '推广成交金额'";
        M()->execute($sql);
    }

    public function table5(){
        $sql = "create table `counsel_article`(
            `id` int unsigned not null primary key auto_increment,
            `title` varchar(50) not null comment '标题',
            `type` tinyint unsigned not null default '0',
            `content` mediumtext ,
            `creator` varchar(30),
            `creator_id` int unsigned ,
            `created_at` timestamp not null default CURRENT_TIMESTAMP
        )comment '投顾部的对股市的分析，股票推荐';";
        M()->execute();
    }

    public function table6(){
        $sql = "alter table `customers_buy` add column deal_time timestamp  null   comment '处理时间';";
        M()->execute($sql);
    }

    public function table7(){
        $sql = "alter table `customers_buy` add column dis_time timestamp  null   comment '分配时间';";
        M()->execute($sql);
    }

    public function table8(){
        $sql = "alter table `customers_buy` add column pay_info text  null   comment '支付信息';";
        M()->execute($sql);
    }

    public function table9(){
        $sql = "alter table `customers_buy` add column  text  null   comment '支付信息';";
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

    //小组主管 单独设立一个角色 叫 "groupCaptian" 
    //所有的 “主管” 角色的 pid 就是这个
    //还有后面添加的 材料组长 回访组长 审查组长
    public function role2(){
        $rId = M("rbac_role")->add(
            array('name'=>"小组主管",
                  'ename'=>'groupCaptian',
                  'pid'=>0,
                  'status'=>1,
                  'remark'=>'所有的小组主管')
        );

        $sql = "update rbac_role set pid=$rId where ename='captain' or ename='supService' or ename='spreadCaptain' ";
        M()->execute($sql);
    }

    public function role3(){
        $rId = M("rbac_role")->add(
            array('name'=>"审查组长",
                  'ename'=>'riskGroup',
                  'pid'=>26,
                  'status'=>1,
                  'remark'=>'审查组长')
        );
    }

    public function role4(){
        
        $rId = M("rbac_role")->add(
            array('name'=>"回访组长",
                  'ename'=>'callBackCaptain',
                  'pid'=>26,
                  'status'=>1,
                  'remark'=>'回访组长')
        );

        $rId = M("rbac_role")->add(
            array('name'=>"材料组长",
                  'ename'=>'dataCaptain',
                  'pid'=>26,
                  'status'=>1,
                  'remark'=>'材料组长')
        );

        $rId = M("rbac_role")->add(
            array('name'=>"投顾组长",
                  'ename'=>'counselorCaptain',
                  'pid'=>26,
                  'status'=>1,
                  'remark'=>'投顾组长')
        );
        
    }

    

    public function rights(){
        $rights = array(
            array(
                'name' => 'GodGroup',
                'pid'  => '1',
                'remark' => '',
                'sort'  => '0',
                'status' => 1,
                'title' => '客户分配',
                'level' => 2,
                'children' => array(
                        array('name'=>'index', 'pid'=>0, 'sort'=>0, 'level'=>3, 'status'=>1, 'title'=>'列表页', 'roles'=>array(1)),
                        array('name'=>'getGroups', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取小组', 'roles'=>array(1)),
                        array('name'=>'getEmployeesByGroupId', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取成员', 'roles'=>array(1)),
                        
                    ),
                'roles' => array(1),
            )
        );

        $this->deal($rights,1);

    }

    public function rights2(){
        $rights = array(
            array(
                'name' => 'PerformanceForSpread',
                'pid'  => '1',
                'remark' => '',
                'sort'  => '0',
                'status' => 1,
                'title' => '业绩报表-推广',
                'level' => 2,
                'children' => array(
                        array('name'=>'index', 'pid'=>0, 'sort'=>0, 'level'=>3, 'status'=>1, 'title'=>'列表页', 'roles'=>array(1,20,21)),
                        array('name'=>'getGroups', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取小组', 'roles'=>array(1,20,21)),
                        array('name'=>'getOrderInfo', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取订单', 'roles'=>array(1,20,21)),
                        array('name'=>'setHz', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取汇总', 'roles'=>array(1,20,21)),
                        
                    ),
                'roles' => array(1,20,21),
            )
        );

        $this->deal($rights,1);

    }



    public function rights3(){
        $rights = array(
            array(
                'name' => 'CounselArticle',
                'pid'  => '1',
                'remark' => '',
                'sort'  => '0',
                'status' => 1,
                'title' => '行情资讯',
                'level' => 2,
                'children' => array(
                        array('name'=>'index', 'pid'=>0, 'sort'=>0, 'level'=>3, 'status'=>1, 'title'=>'列表页', 'roles'=>array(1,23,25,31)),
                        array('name'=>'add', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'添加', 'roles'=>array(1,23,25,31)),
                        array('name'=>'edit', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'编辑', 'roles'=>array(1,23,25,31)),
                        array('name'=>'delete', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'删除', 'roles'=>array(1,23,25,31)),
                        
                        
                    ),
                'roles' => array(1,23,25,31),
            )
        );

        $this->deal($rights,1);

    }


    public function rights4(){
        $rights = array(
            array(
                'name' => 'CounselArticleView',
                'pid'  => '1',
                'remark' => '',
                'sort'  => '0',
                'status' => 1,
                'title' => '行情资讯-查看',
                'level' => 2,
                'children' => array(
                        array('name'=>'index', 'pid'=>0, 'sort'=>0, 'level'=>3, 'status'=>1, 'title'=>'列表页', 'roles'=>array())   
                    ),
                'roles' => array(),
            )
        );

        $this->deal($rights,1);

    }


    public function menu(){
        M('menu_basic')->add(array(
            'pid' => 25,
            'icon' => '&#xe655',
            'title' => '行情资讯',
            'href' => 'CounseArticle/index',
            'node_id'=>0,
            'sort' => 0
        ));
    }

    public function menu2(){
        M('menu_basic')->add(array(
            'pid' => 25,
            'icon' => '&#xe655',
            'title' => '行情资讯-查看',
            'href' => 'CounselArticleView/index',
            'node_id'=>0,
            'sort' => 0
        ));
    }


    private function deal($rights, $pid=1){
        foreach ($rights as $value) {

            $nodeM = M("rbac_node");
            $value['pid'] = $pid;
            $node = $nodeM->create($value);
            if (!$node) {
                // var_dump($value);
                echo "fail";
                echo "\n";
                return;
            }
            $node_id = $nodeM->add();
            if (!$node_id) {
                echo "insert into fail";
                var_dump($node);
                echo "\n";
                return ;
            }
            if ($value['children']) {
                $this->deal($value['children'], $node_id);
            }

            $this->dealRole($node_id, $value['roles'], $value['name'], $value['level']);
        }

    }

    private function dealRole($nodeId, $roles, $module, $level){
        $accessM = M("rbac_access");
        foreach ($roles as $roleId) {
            $data = array(
                'role_id'=>$roleId,
                'node_id'=>$nodeId,
                'level'  => $level,
                'module' => $module
            );
            $accessM->add($data);
        }
    }

}