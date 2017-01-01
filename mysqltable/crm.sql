-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        5.6.19 - MySQL Community Server (GPL)
-- 服务器操作系统:                      Win64
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客户表 ';

-- 数据导出被取消选择。


-- 导出  表 dev_cn_crm_01.department_basic 结构
DROP TABLE IF EXISTS `department_basic`;
CREATE TABLE IF NOT EXISTS `department_basic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '部门名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组织单位';

-- 数据导出被取消选择。


-- 导出  表 dev_cn_crm_01.menu_basic 结构
DROP TABLE IF EXISTS `menu_basic`;
CREATE TABLE IF NOT EXISTS `menu_basic` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `pid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `icon` varchar(20) DEFAULT NULL,
  `title` varchar(20) NOT NULL,
  `href` varchar(30) DEFAULT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


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

-- 数据导出被取消选择。


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


-- 导出  表 dev_cn_crm_01.rbac_role_user 结构
DROP TABLE IF EXISTS `rbac_role_user`;
CREATE TABLE IF NOT EXISTS `rbac_role_user` (
  `role_id` mediumint(9) unsigned DEFAULT NULL,
  `user_id` char(32) DEFAULT NULL,
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
