<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>CRM客户关系管理系统</title>
		<meta name="renderer" content="webkit">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="format-detection" content="telephone=no">
		<link rel="stylesheet" href="/crm/Public/plugins/layui/css/layui.css" media="all" />
		<link rel="stylesheet" href="/crm/Public/css/global.css" media="all">
		<link rel="stylesheet" href="/crm/Public/plugins/font-awesome/css/font-awesome.min.css">
	</head>
	<body>
		<div class="layui-layout layui-layout-admin">
			<div class="layui-header header header-demo">
				<div class="layui-main">
					<div class="admin-login-box">
						<a class="logo" style="left: 0;" href="#">
							<a style="font-size:22px;line-height:55px;">CRM客户关系管理</a>
						</a>
						<div class="admin-side-toggle">
							<i class="fa fa-bars" aria-hidden="true"></i>
						</div>
					</div>
					<ul class="layui-nav admin-header-item" lay-filter="user">
						<li class="layui-nav-item">
							<a href="javascript:;">清除缓存</a>
						</li>
						<li class="layui-nav-item">
							<a href="javascript:;">浏览网站</a>
						</li>
						<li class="layui-nav-item">
							<a href="javascript:;" class="admin-header-user">
								<img src="/crm/Public/images/0.jpg" />
								<span>riign_chengdu</span>
							</a>
							<dl class="layui-nav-child">
								<dd>
									<a data-tab="true" data-url='javascript:;'><i class="layui-icon">&#xe629;</i> <cite>个人信息</cite></a>
								</dd>
								<dd>
									<a data-tab="true" data-url='javascript:;'><i class="layui-icon">&#xe620;</i> <cite>设置</cite></a>
								</dd>
								<dd>
									<a href="login.html"><i class="fa fa-sign-out" aria-hidden="true"></i> 注销</a>
								</dd>
							</dl>
						</li>
					</ul>
				</div>
			</div>
			<div class="layui-side layui-bg-black" id="admin-side">
				<div class="layui-side-scroll" id="admin-navbar-side" lay-filter="side"></div>
			</div>
			<div class="layui-body" style="top: 57px;" id="admin-body">
				<div class="layui-tab admin-nav-card layui-tab-brief" lay-filter="admin-tab">
					<ul class="layui-tab-title">
						<li class="layui-this">
							<i class="layui-icon" aria-hidden="true">&#xe656;</i>
							<cite>我的工作台</cite>
						</li>
					</ul>
					<div class="layui-tab-content" style="min-height: 150px; padding: 5px 0 0 0;">
						<div class="layui-tab-item layui-show">
							<iframe src="<?php echo U('Index/main');?>"></iframe>
						</div>
					</div>
				</div>
			</div>
			<div class="site-tree-mobile layui-hide">
				<i class="layui-icon">&#xe602;</i>
			</div>
			<div class="site-mobile-shade"></div>
			<script type="text/javascript" src="/crm/Public/plugins/layui/layui.js"></script>
			<!-- <script type="text/javascript" src="/crm/Public/datas/nav.js" ></script> -->
			<script type="text/javascript" src="<?php echo U('Nav/index');?>" ></script>
			<script src="/crm/Public/js/index.js"></script>
		</div>
	</body>

</html>