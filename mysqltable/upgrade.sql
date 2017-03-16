alter table customers_basic add column `salesman_id` int unsigned not null after `user_id`;
alter table customers_basic add column `service_time` INT(11) NOT NULL COMMENT '服务时间 用于确定 "6个月未成交" 的判断' after `created_at`;
alter table customers_basic add column `status` tinyint NOT NULL default '1' ;


alter table customers_basic change column `phone`           `phone_del` char(11);
alter table customers_basic change column `phone2`          `phone2_del` char(11);
alter table customers_basic change column `qq`              `qq_del` varchar(15);
alter table customers_basic change column `qq2`             `qq2_del` varchar(15);
alter table customers_basic change column `qq_nickname`     `qq_nickname_del` varchar(20);
alter table customers_basic change column `qq_nickname2`    `qq_nickname2_del`   varchar(20);
alter table customers_basic change column `weixin`          `weixin_del`   varchar(20);
alter table customers_basic change column `weixin_nickname` `weixin_nickname_del`   varchar(20);



alter table `rbac_user` add column  `ip` VARCHAR(20) NOT NULL COMMENT '登录ip';
alter table `rbac_user` add column	`location` VARCHAR(30) NOT NULL COMMENT '登录地区';
alter table `rbac_user` add column	`lg_time` VARCHAR(15) NOT NULL COMMENT '登录时间';

alter table `rbac_user` change colUMN `status` `status`   tinyint not null default '1'; 


DROP TRIGGER IF EXISTS customers_log_after_insert;
CREATE TRIGGER customers_log_after_insert 
AFTER INSERT ON customers_log
FOR EACH ROW
BEGIN

if NEW.next_datetime is null then
UPDATE `customers_basic` set `log_count` =  `log_count` +1 where id = NEW.cus_id;
else 
UPDATE `customers_basic` set `log_count` =  `log_count` +1, `plan` = NEW.next_datetime where id = NEW.cus_id;
end if;

update `customers_basic` set `last_track` = NEW.created_at where id = NEW.cus_id;

END;



CREATE TABLE `customers_pulls` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`from_id` INT(11) NOT NULL COMMENT '源自哪个员工 customers_basic salseman_id',
	`to_id` INT(11) NOT NULL COMMENT '员工id',
	`cus_id` INT(11) NOT NULL COMMENT '客户id',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COMMENT='索取的记录'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `customers_contacts` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`cus_id` INT(10) UNSIGNED NOT NULL COMMENT '客户id',
	`phone` CHAR(20) NULL DEFAULT NULL,
	`qq` VARCHAR(15) NULL DEFAULT NULL,
	`weixin` VARCHAR(20) NULL DEFAULT NULL,
	`qq_nickname` VARCHAR(50) NULL DEFAULT NULL,
	`weixin_nickname` VARCHAR(50) NULL DEFAULT NULL,
	`is_main` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是不是第一个输入的 1 是 0 不是',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `phone` (`phone`),
	UNIQUE INDEX `qq` (`qq`),
	UNIQUE INDEX `weixin` (`weixin`)
)
COMMENT='客户的通讯信息'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `customer_transflog` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`cus_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '客户id',
	`from_id` INT(11) NOT NULL COMMENT '来源员工id',
	`from_group_id` INT(11) NOT NULL COMMENT '来源小组id',
	`from_department_id` INT(11) NOT NULL COMMENT '来源部门id',
	`to_id` INT(11) NULL DEFAULT NULL COMMENT '目标员工id',
	`to_group_id` INT(11) NULL DEFAULT NULL COMMENT '目标小组id',
	`to_department_id` INT(11) NOT NULL COMMENT '目标部门id',
	`content` VARCHAR(100) NOT NULL COMMENT '备注',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `deal_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id 序号',
	`user_id` INT(10) UNSIGNED NOT NULL COMMENT '销售id',
	`cus_id` INT(10) UNSIGNED NOT NULL COMMENT '客户id',
	`identity` VARCHAR(20) NOT NULL COMMENT '身份证号',
	`type` TINYINT(3) UNSIGNED NOT NULL COMMENT '产品类型',
	`expense` VARCHAR(15) NOT NULL COMMENT '产品金额',
	`cycle` TINYINT(3) UNSIGNED NOT NULL COMMENT '服务周期',
	`address` VARCHAR(50) NOT NULL COMMENT '通讯地址',
	`time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `identity` (`identity`),
	UNIQUE INDEX `cus_id` (`cus_id`)
)
COMMENT='真实资料(交易信息)表'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


CREATE TABLE `sys_notice` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id 序号',
	`type` INT(4) UNSIGNED NULL DEFAULT NULL COMMENT '公告类型',
	`title` VARCHAR(40) NOT NULL COMMENT '标题相当于内容40字',
	`content` VARCHAR(140) NOT NULL COMMENT '公告内容140字',
	`start` DATETIME NOT NULL COMMENT '开始时间',
	`end` DATETIME NOT NULL COMMENT '截止时间',
	`user_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '发布人id',
	`time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '公告创建时间',
	PRIMARY KEY (`id`)
)
COMMENT='系统通知表'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `msgbox_basic` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(50) NOT NULL COMMENT '消息标题',
	`content` VARCHAR(200) NOT NULL COMMENT '消息内容',
	`time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '消息发送时间',
	`from_id` INT(10) UNSIGNED NOT NULL COMMENT '发送人user_id',
	`to_id` INT(10) UNSIGNED NOT NULL COMMENT '接收人user_id',
	PRIMARY KEY (`id`)
)
COMMENT='消息盒子'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;







