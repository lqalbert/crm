<?php

define('CRM_SUPER_ADMIN', 1);


//行为
define('HOOK_PRECHECK', 'precheck_que');
define('HOOK_ADDCONTACT', 'addContact_que');
define('HOOK_DISTRIBUTE_BUY_CUSTOMER', 'disBuyCustomer_que');
define('HOOK_CHECK', 'check_que');// 审核
define('HOOK_QUEUE', 'queue');// 审核

define('BETA_PROMOTION',   "http://beta.riign.cn/promotion?id=");//测试推广页
define('BETA_PREVIEW',   "http://beta.riign.cn/promotion/Preview?p=");//测试推广页预览
define('BETA_TEST1',   "http://beta.riign.cn/promotion/Test1?p=");//测试推广页Test1预览
define('BETA_PROMATERIAL',   "http://beta.riign.cn/promaterial");//测试推广页单页面promaterial
define('BETA_CRM_URL',   "http://127.0.0.1/crm");//本CRM网络地址
define('BETA_PROMOTION_DB','mysql://beta_spread:beta2008beta@139.224.40.238:3306/beta_spread#utf8');

define('DEFALT_QUEUE', 'queue1'); //默认的队列名称

define('DIRROOT', dirname(__FILE__));
