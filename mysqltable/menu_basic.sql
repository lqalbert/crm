-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        5.6.17 - MySQL Community Server (GPL)
-- 服务器操作系统:                      Win32
-- HeidiSQL 版本:                  9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出  表 dev_crm_02.menu_basic 结构
CREATE TABLE IF NOT EXISTS `menu_basic` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `pid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `icon` varchar(20) DEFAULT NULL,
  `title` varchar(20) NOT NULL,
  `href` varchar(30) DEFAULT NULL,
  `node_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;

-- 正在导出表  dev_crm_02.menu_basic 的数据：~41 rows (大约)
/*!40000 ALTER TABLE `menu_basic` DISABLE KEYS */;
INSERT INTO `menu_basic` (`id`, `pid`, `icon`, `title`, `href`, `node_id`, `sort`) VALUES
	(7, 0, '&#xe6f6;', '百宝箱', '', 0, 0),
	(8, 7, '&nbsp;&#xe656;', '我的工作台', 'Index/main', 38, 0),
	(9, 7, '&nbsp;&#xe72e;', '消息盒子', 'MsgBox/index', 0, 1),
	(10, 7, '&nbsp;&#xe65d;', '系统公告', 'SysNotice/index', 0, 2),
	(11, 7, '&nbsp;&#xe624;', '集思信箱', 'GatherAdvice/index', 0, 3),
	(12, 7, '&nbsp;&#xe660;', '工作总结', 'WorkSummary/index', 0, 4),
	(13, 7, '&nbsp;&#xe61e;', '培训学院', 'Index/up', 0, 5),
	(14, 7, '&nbsp;&#xe76b;', '素材库', 'Index/material', 0, 6),
	(15, 7, '&nbsp;&#xe613;', '系统管理', 'javascript:;', 0, 7),
	(16, 0, '&#xe6a0;', 'CPS模块', '', 0, 0),
	(17, 16, '&nbsp;&#xe609;', '短网址URL', 'javascript:;', 0, 0),
	(18, 0, '&#xe623;', '组织员工', '', 0, 0),
	(19, 18, '&nbsp;&#xe61f;', '组织单位', 'Department/index', 22, 0),
	(20, 18, '&nbsp;&#xe608;', '团队小组', 'Group/index', 26, 1),
	(21, 18, '&nbsp;&#xe65a;', '员工管理', 'Employee/index', 30, 2),
	(22, 18, '&nbsp;&#xe625;', '角色管理', 'Role/index', 0, 3),
	(23, 18, '&nbsp;&#xe62d;', '权限管理', 'Node/index', 0, 4),
	(24, 18, '&nbsp;&#xe60c;', '菜单管理', 'Menu/index', 0, 5),
	(25, 0, '&#xe614;', '日常工作', '', 0, 0),
	(26, 25, '&nbsp;&#xe67d;', '客户管理', 'Customer/index', 2, 0),
	(27, 25, '&nbsp;&#xe650;', '资料接收', 'Customer/index', 0, 1),
	(28, 0, '&#xe657;', '统计报表', '', 0, 0),
	(29, 28, '&nbsp;&#xe6f2;', '量化报表', 'javascript:;', 0, 0),
	(30, 28, '&nbsp;&#xe6bf;', '客户报表', 'javascript:;', 0, 1),
	(31, 28, '&nbsp;&#xe61c;', '客户统计', 'javascript:;', 0, 2),
	(32, 28, '&nbsp;&#xe66b;', '业绩报表', 'javascript:;', 0, 3),
	(33, 28, '&nbsp;&#xe636;', '续费报表', 'javascript:;', 0, 4),
	(34, 28, '&nbsp;&#xe6a9;', '业绩排名', 'javascript:;', 0, 5),
	(35, 28, '&nbsp;&#xe601;', '资料跟踪', 'javascript:;', 0, 6),
	(36, 0, '&#xe6f0;', '业务助手', '', 0, 0),
	(37, 36, '&nbsp;&#xe967;', 'Q群扫描', 'javascript:;', 0, 0),
	(38, 36, '&nbsp;&#xe693;', '手机预查', 'javascript:;', 0, 1),
	(39, 36, '&nbsp;&#xe6a4;', '异常IP', 'javascript:;', 0, 2),
	(40, 36, '&nbsp;&#xe6ec;', '回收站', 'javascript:;', 0, 3),
	(42, 7, '&nbsp;&#xe73f;', '资料导入', 'Excel/index', 42, 8),
	(43, 25, '&nbsp;&#xe674;', '客户预查', 'PreCheck/index', 49, 2),
	(44, 28, '&nbsp;&#xe66c;', '录入统计', 'AddCount/index', 63, 7),
	(45, 28, '&nbsp;&#xe655;', '录入排序', 'SortCustomersCount/index', 64, 8),
	(46, 18, '&nbsp;&#xe61d;', '组织区', 'DepartmentDivision/index', 65, 0),
	(47, 25, '&nbsp;&#xe62e;', '部门客户', 'DepartmentCustomer/index', 69, 3),
	(48, 25, '&nbsp;&#xe683;', '客服服务', 'CustomerServer/index', 0, 4),
	(49, 0, '&#xe683;', '客服服务', '', 0, 0),
	(50, 49, '&nbsp;&#xe611;', '风控员一', 'RiskCtrlOne/index', 0, 0),
	(51, 49, '&nbsp;&#xe619;', '回访专员', 'CallBack/index', 0, 0),
	(52, 49, '&nbsp;&#xe61e;', '客服主管', 'ServiceSupervisor/index', 0, 0),
	(53, 49, '&nbsp;&#xe626;', '普通客服', 'GeneralService/index', 0, 0);
/*!40000 ALTER TABLE `menu_basic` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
