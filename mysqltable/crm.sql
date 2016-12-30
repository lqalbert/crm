-- --------------------------------------------------------
-- 主机:                           192.168.0.12
-- 服务器版本:                        5.5.52-MariaDB - MariaDB Server
-- 服务器操作系统:                      Linux
-- HeidiSQL 版本:                  9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出 dev_cn_crm_01 的数据库结构
DROP DATABASE IF EXISTS `dev_cn_crm_01`;
CREATE DATABASE IF NOT EXISTS `dev_cn_crm_01` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `dev_cn_crm_01`;


-- 导出  表 dev_cn_crm_01.customers_basic 结构
DROP TABLE IF EXISTS `customers_basic`;
CREATE TABLE IF NOT EXISTS `customers_basic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '客户类型 请查看文档',
  `phone` char(11) NOT NULL DEFAULT '0' COMMENT '手机号',
  `qq` varchar(15) NOT NULL DEFAULT '0' COMMENT 'QQ',
  `qq_nickname` varchar(20) NOT NULL DEFAULT '0' COMMENT 'QQ昵称',
  `weixin` varchar(20) NOT NULL DEFAULT '0' COMMENT '微信号',
  `weixin_nickname` varchar(20) NOT NULL DEFAULT '0' COMMENT '微信昵称',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '员工 id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Index 2` (`phone`),
  UNIQUE KEY `Index 3` (`qq`),
  UNIQUE KEY `Index 4` (`weixin`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='客户表 ';

-- 正在导出表  dev_cn_crm_01.customers_basic 的数据：~2 rows (大约)
/*!40000 ALTER TABLE `customers_basic` DISABLE KEYS */;
INSERT INTO `customers_basic` (`id`, `name`, `type`, `phone`, `qq`, `qq_nickname`, `weixin`, `weixin_nickname`, `user_id`) VALUES
	(1, '7立', 4, '123', '11', '0', '23', '0', 0),
	(2, 'awefweffff', 3, '0', '0', '0', '0', '0', 1),
	(3, 'feawef', 0, '123123123', '123123', '1231', '23123', '123123', 1),
	(4, 'awfawfaf', 2, '1111', '111', '111', '111', '11111', 0);
/*!40000 ALTER TABLE `customers_basic` ENABLE KEYS */;


-- 导出  表 dev_cn_crm_01.department_basic 结构
DROP TABLE IF EXISTS `department_basic`;
CREATE TABLE IF NOT EXISTS `department_basic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '部门名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COMMENT='组织单位';

-- 正在导出表  dev_cn_crm_01.department_basic 的数据：~4 rows (大约)
/*!40000 ALTER TABLE `department_basic` DISABLE KEYS */;
INSERT INTO `department_basic` (`id`, `name`) VALUES
	(53, 'agergrg'),
	(54, 'aaaaaaaaaaaaaa'),
	(55, 'aggggggggg'),
	(56, 'addddddddddddg');
/*!40000 ALTER TABLE `department_basic` ENABLE KEYS */;


-- 导出  表 dev_cn_crm_01.rbac_access 结构
DROP TABLE IF EXISTS `rbac_access`;
CREATE TABLE IF NOT EXISTS `rbac_access` (
  `role_id` smallint(6) unsigned NOT NULL,
  `node_id` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 正在导出表  dev_cn_crm_01.rbac_access 的数据：~32 rows (大约)
/*!40000 ALTER TABLE `rbac_access` DISABLE KEYS */;
INSERT INTO `rbac_access` (`role_id`, `node_id`, `level`, `module`) VALUES
	(2, 1, 1, 'Home'),
	(2, 2, 2, 'Customer'),
	(2, 22, 2, 'Department'),
	(2, 26, 2, 'Group'),
	(2, 19, 3, 'add'),
	(2, 24, 3, 'edit'),
	(2, 27, 3, 'add'),
	(2, 29, 3, 'delete'),
	(3, 1, 1, 'Home'),
	(3, 2, 2, 'Customer'),
	(3, 22, 2, 'Department'),
	(3, 26, 2, 'Group'),
	(3, 30, 2, 'Employee'),
	(3, 19, 3, 'add'),
	(3, 24, 3, 'edit'),
	(3, 27, 3, 'add'),
	(3, 32, 3, 'edit'),
	(4, 1, 1, 'Home'),
	(4, 2, 2, 'Customer'),
	(4, 22, 2, 'Department'),
	(4, 26, 2, 'Group'),
	(4, 19, 3, 'add'),
	(4, 23, 3, 'add'),
	(4, 24, 3, 'edit'),
	(4, 25, 3, 'delete'),
	(4, 27, 3, 'add'),
	(4, 29, 3, 'delete'),
	(1, 1, 1, 'Home'),
	(1, 2, 2, 'Customer'),
	(1, 19, 3, 'add'),
	(1, 20, 3, 'edit'),
	(1, 21, 3, 'delete'),
	(1, 34, 3, 'index');
/*!40000 ALTER TABLE `rbac_access` ENABLE KEYS */;


-- 导出  表 dev_cn_crm_01.rbac_node 结构
DROP TABLE IF EXISTS `rbac_node`;
CREATE TABLE IF NOT EXISTS `rbac_node` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `sort` smallint(6) unsigned NOT NULL DEFAULT '0',
  `pid` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `pid` (`pid`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

-- 正在导出表  dev_cn_crm_01.rbac_node 的数据：~21 rows (大约)
/*!40000 ALTER TABLE `rbac_node` DISABLE KEYS */;
INSERT INTO `rbac_node` (`id`, `name`, `title`, `status`, `remark`, `sort`, `pid`, `level`) VALUES
	(1, 'Home', 'Home', 1, 'a', 0, 0, 1),
	(2, 'Customer', '客户管理', 1, '客户管理', 0, 1, 2),
	(19, 'add', '添加用户', 1, '添加用户', 1, 2, 3),
	(20, 'edit', '编辑用户', 1, '编辑用户', 2, 2, 3),
	(21, 'delete', '删除用户', 1, '编辑用户', 3, 2, 3),
	(22, 'Department', '组织单位', 1, '组织单位', 0, 1, 2),
	(23, 'add', '添加组织单位', 1, '添加组织单位', 1, 22, 3),
	(24, 'edit', '编辑组织单位', 1, '编辑组织单位', 2, 22, 3),
	(25, 'delete', '删除组织单位', 1, '删除组织单位', 3, 22, 3),
	(26, 'Group', '团队小组', 1, '团队小组', 0, 1, 2),
	(27, 'add', '添加小组', 1, '添加小组', 1, 26, 3),
	(28, 'edit', '编辑小组', 1, '编辑小组', 2, 26, 3),
	(29, 'delete', '删除小组', 1, '删除小组', 3, 26, 3),
	(30, 'Employee', '员工管理', 1, '员工管理', 0, 1, 2),
	(31, 'add', '添加员工', 1, '添加员工', 1, 30, 3),
	(32, 'edit', '编辑员工', 1, '编辑员工', 2, 30, 3),
	(33, 'delete', '删除员工', 1, '删除员工', 3, 30, 3),
	(34, 'index', '客户列表', 1, '客户列表', 0, 2, 3),
	(35, 'index', '员工列表', 1, '员工列表', 0, 30, 3),
	(36, 'index', '小组列表', 1, '小组列表', 0, 26, 3),
	(37, 'index', '单位列表', 1, '单位列表', 0, 22, 3);
/*!40000 ALTER TABLE `rbac_node` ENABLE KEYS */;


-- 导出  表 dev_cn_crm_01.rbac_role 结构
DROP TABLE IF EXISTS `rbac_role`;
CREATE TABLE IF NOT EXISTS `rbac_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `pid` smallint(6) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 禁用 1 启用 －1 删除',
  `remark` varchar(255) DEFAULT NULL COMMENT '角色说明',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- 正在导出表  dev_cn_crm_01.rbac_role 的数据：~4 rows (大约)
/*!40000 ALTER TABLE `rbac_role` DISABLE KEYS */;
INSERT INTO `rbac_role` (`id`, `name`, `pid`, `status`, `remark`) VALUES
	(1, '员工', 0, 1, '员工 '),
	(2, '总经理', 0, 1, '总经理'),
	(3, '主管', 0, 1, '主管'),
	(4, '总经办', 0, 1, '总经办');
/*!40000 ALTER TABLE `rbac_role` ENABLE KEYS */;


-- 导出  表 dev_cn_crm_01.rbac_role_user 结构
DROP TABLE IF EXISTS `rbac_role_user`;
CREATE TABLE IF NOT EXISTS `rbac_role_user` (
  `role_id` mediumint(9) unsigned DEFAULT NULL,
  `user_id` char(32) DEFAULT NULL,
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 正在导出表  dev_cn_crm_01.rbac_role_user 的数据：~3 rows (大约)
/*!40000 ALTER TABLE `rbac_role_user` DISABLE KEYS */;
INSERT INTO `rbac_role_user` (`role_id`, `user_id`) VALUES
	(3, '1'),
	(4, '1'),
	(1, '3'),
	(1, '4');
/*!40000 ALTER TABLE `rbac_role_user` ENABLE KEYS */;


-- 导出  表 dev_cn_crm_01.rbac_user 结构
DROP TABLE IF EXISTS `rbac_user`;
CREATE TABLE IF NOT EXISTS `rbac_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(20) NOT NULL,
  `password` char(32) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `no_authorized` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 默认 1 superadmin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account` (`account`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- 正在导出表  dev_cn_crm_01.rbac_user 的数据：~2 rows (大约)
/*!40000 ALTER TABLE `rbac_user` DISABLE KEYS */;
INSERT INTO `rbac_user` (`id`, `account`, `password`, `status`, `created_at`, `no_authorized`) VALUES
	(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 1, '2016-12-19 21:49:25', 1),
	(3, 'bbb', 'e10adc3949ba59abbe56e057f20f883e', 1, '2016-12-29 04:18:03', 0),
	(4, 'abc', 'e10adc3949ba59abbe56e057f20f883e', 0, '2016-12-30 01:51:21', 0);
/*!40000 ALTER TABLE `rbac_user` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
