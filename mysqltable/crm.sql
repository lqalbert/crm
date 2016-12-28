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

-- 导出 dev_cn_crm 的数据库结构


-- 导出  表 dev_cn_crm.department_basic 结构
DROP TABLE IF EXISTS `department_basic`;
CREATE TABLE IF NOT EXISTS `department_basic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '部门名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COMMENT='组织单位';

-- 正在导出表  dev_cn_crm.department_basic 的数据：~39 rows (大约)
/*!40000 ALTER TABLE `department_basic` DISABLE KEYS */;
INSERT INTO `department_basic` (`id`, `name`) VALUES
	(14, 'aergrrrrr'),
	(15, 'e5yejjyyy'),
	(16, 'e5yejjejetyj'),
	(17, 'e5yejjejetyj'),
	(18, 'wefawfawfe'),
	(19, 'wrtgwrtg'),
	(20, 'wrtgwrtg............'),
	(21, '9999999999999999'),
	(22, '00000000000000'),
	(23, 'k-view-18.html'),
	(24, 'aergaergaerg'),
	(25, 'aergaergatyjtujyukyk'),
	(26, 'aergaergaddddddddddd'),
	(27, ''),
	(28, 'cccc'),
	(29, ''),
	(30, '232312312'),
	(31, 'sdsd'),
	(32, '121212'),
	(33, '1221111111111111111'),
	(34, 'sdsd'),
	(35, '4114141414141414'),
	(36, 'qwqwqwqwq'),
	(37, '121212'),
	(38, 'ewewewewe'),
	(39, 'ewewewewe'),
	(40, '121212'),
	(41, ''),
	(42, ''),
	(43, ''),
	(44, ''),
	(45, ''),
	(46, ''),
	(47, ''),
	(48, ''),
	(49, ''),
	(50, '7uu7u'),
	(51, 'awefawef'),
	(52, 'thrthrth');
/*!40000 ALTER TABLE `department_basic` ENABLE KEYS */;


-- 导出  表 dev_cn_crm.rbac_access 结构
DROP TABLE IF EXISTS `rbac_access`;
CREATE TABLE IF NOT EXISTS `rbac_access` (
  `role_id` smallint(6) unsigned NOT NULL,
  `node_id` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 正在导出表  dev_cn_crm.rbac_access 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `rbac_access` DISABLE KEYS */;
/*!40000 ALTER TABLE `rbac_access` ENABLE KEYS */;


-- 导出  表 dev_cn_crm.rbac_node 结构
DROP TABLE IF EXISTS `rbac_node`;
CREATE TABLE IF NOT EXISTS `rbac_node` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `sort` smallint(6) unsigned DEFAULT NULL,
  `pid` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `pid` (`pid`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- 正在导出表  dev_cn_crm.rbac_node 的数据：~6 rows (大约)
/*!40000 ALTER TABLE `rbac_node` DISABLE KEYS */;
INSERT INTO `rbac_node` (`id`, `name`, `title`, `status`, `remark`, `sort`, `pid`, `level`) VALUES
	(1, 'Home', NULL, 1, 'a', NULL, 0, 1),
	(2, '用户管理', NULL, 1, '用户管理', NULL, 1, 2),
	(3, ';l/l;/l;/l;/l;/', NULL, 1, 'l;/l;/', NULL, 1, 2),
	(4, '020', NULL, 1, '', NULL, 2, 3),
	(5, 'ip;p', ';ip;oi;', 1, 'io;io;', NULL, 3, 3),
	(6, 'ioio;', 'i;io;', 1, 'i;io;io;', NULL, 2, 3);
/*!40000 ALTER TABLE `rbac_node` ENABLE KEYS */;


-- 导出  表 dev_cn_crm.rbac_role 结构
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- 正在导出表  dev_cn_crm.rbac_role 的数据：~3 rows (大约)
/*!40000 ALTER TABLE `rbac_role` DISABLE KEYS */;
INSERT INTO `rbac_role` (`id`, `name`, `pid`, `status`, `remark`) VALUES
	(1, '部门主管', 0, 1, '主管部门aaabbb'),
	(2, '总经理', 0, 1, 'godujuj'),
	(3, 'aaaaaaaaaaa', 0, 0, 'awefawefawefawf');
/*!40000 ALTER TABLE `rbac_role` ENABLE KEYS */;


-- 导出  表 dev_cn_crm.rbac_role_user 结构
DROP TABLE IF EXISTS `rbac_role_user`;
CREATE TABLE IF NOT EXISTS `rbac_role_user` (
  `role_id` mediumint(9) unsigned DEFAULT NULL,
  `user_id` char(32) DEFAULT NULL,
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 正在导出表  dev_cn_crm.rbac_role_user 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `rbac_role_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `rbac_role_user` ENABLE KEYS */;


-- 导出  表 dev_cn_crm.rbac_user 结构
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- 正在导出表  dev_cn_crm.rbac_user 的数据：~1 rows (大约)
/*!40000 ALTER TABLE `rbac_user` DISABLE KEYS */;
INSERT INTO `rbac_user` (`id`, `account`, `password`, `status`, `created_at`, `no_authorized`) VALUES
	(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 1, '2016-12-19 21:49:25', 1);
/*!40000 ALTER TABLE `rbac_user` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
