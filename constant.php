<?php

define('CRM_SUPER_ADMIN', 1);


//行为
define('HOOK_PRECHECK', 'precheck_que');
define('HOOK_ADDCONTACT', 'addContact_que');
define('HOOK_DISTRIBUTE_BUY_CUSTOMER', 'disBuyCustomer_que');
define('HOOK_CHECK', 'check_que');// 审核
define('HOOK_QUEUE', 'queue');// 审核

define('BETA_PROMOTION',   "http://beta.riign.cn/promotion?id=");//测试推广页
define('BETA_PROMOTION_DB','mysql://beta_spread:beta2008beta@139.224.40.238:3306/beta_spread#utf8');

define('DEFALT_QUEUE', 'queue1'); //默认的队列名称