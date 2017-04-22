<?php
return array(
	//'配置项'=>'配置值'
	'LOAD_EXT_CONFIG' => 'db',

    //调试
	'SHOW_PAGE_TRACE' => false, 


	//语言
	'LANG_SWITCH_ON'   => true,   // 开启语言包功能
	'LANG_AUTO_DETECT' => true, // 自动侦测语言 开启多语言功能后有效
	'LANG_LIST'        => 'zh-cn', // 允许切换的语言列表 用逗号分隔
	'VAR_LANGUAGE'     => 'l', // 默认语言切换变量



	//权限验证
	'USER_AUTH_ON' => true,      // 是否需要认证
	'USER_AUTH_TYPE' => 1,       // 认证类型
	'USER_AUTH_KEY' => 'uid',    // 认证识别号
	//REQUIRE_AUTH_MODULE  需要认证模块
	'NOT_AUTH_MODULE' => 'Index,nav,Area,PreCheck,Upload,UserDetail,Department,SysNotice,MsgBox,CommonFindDetail,CustomersCountSecond',// 无需认证模块

	'NOT_AUTH_ACTION' =>  'main,index,getList,checkCucstomers,importMyc,getUsers,getdepartgroups,getRecUser,findRealInfo,realInfo,trasnfCustomers', //无需认证操作
	//USER_AUTH_GATEWAY 认证网关
	//RBAC_DB_DSN  数据库连接DSN
	'RBAC_ROLE_TABLE' => 'rbac_role',      //角色表名称  
	'RBAC_USER_TABLE'=>  'rbac_role_user', //用户与角色的中间表  
	'RBAC_ACCESS_TABLE'=>'rbac_access',   //权限表  
	'RBAC_NODE_TABLE'=>  'rbac_node',       //节点表  
	// 'RBAC_SUPERADMIN' => 'admin',
	'ADMIN_AUTH_KEY'=>'superAdmin',       //超级管理员识别
);
