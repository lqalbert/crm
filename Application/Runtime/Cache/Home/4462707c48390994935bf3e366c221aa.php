<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
	<meta charset="UTF-8">
	<title>后台管理 原型示例</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<link rel="stylesheet" href="/crm/Public/plugins/layui/css/layui.css" media="all" />
 	<!-- <link rel="stylesheet" href="/crm/Public/css/global.css" media="all"> -->
	<!--<link rel="stylesheet" href="/crm/Public/plugins/font-awesome/css/font-awesome.min.css"> -->

	<link rel="stylesheet" href="/crm/Public/css/element-ui--index.css">

	<style>
		.pull-left {
			float: left;
		}
		.pull-right {
			float: right;
		}
		.container {
			padding: 10px;
		}
		.container > .wrapp > .el-row {
			margin-bottom: 20px;
		}
		.el-form--inline .el-form-item {
			margin-bottom: 0px;
		}
		/*#app>.wrapp{
			display: none;
		}*/


		
	</style>
	<!-- 如果没有 则 提供一些高级的js功能  -->
	<script src="/crm/Public/js/advancejs.js"></script>

	<!-- vue 通用部分 -->
	<script src="/crm/Public/js/vueapp-base.js"></script>
	<!-- / vue 通用部分 -->

	<!-- 不支持IE8 及以下的浏览器 -->
	<!--[if lte IE 8]> 
	<script>
		window.location.href="<?php echo U('notsupport');?>";
	</script>
	<![endif]-->

	
</head>
<body>
	<div style="margin:10px;">
		<div class="el-alert el-alert--warning" ><i class="el-alert__icon el-icon-information"></i><div class="el-alert__content"><span class="el-alert__title">此页面仅作为需求讨论的参考与演示，不具有对应的功能，也不代表最终项目的功能。</span><!----><i class="el-alert__closebtn el-icon-close"></i></div></div>
	</div>
	
	

<div id="app" class="container">
<div class="wrapp" v-show="show" style="display: none;">

	<el-row :gutter="20">
		<el-col :span="8">
			<el-card class="box-card">
			  <div slot="header" class="clearfix">
			    <span>个人信息</span>
			  </div>
			  <div v-for="o in 4" class="text item">
			    {{'列表内容 ' + o }}
			  </div>
			</el-card>
		</el-col>
		<el-col :span="16">
			<el-card class="box-card">
				<div slot="header" class="clearfix">
					系统公告
				</div>

			</el-card>
		</el-col>
	</el-row>
	<el-row>
		<el-col :span="24">
			<el-card class="box-card">
				<div slot="header" class="clearfix">
					<span>业绩走势</span>
				</div>
				<div>
					<div id="echarts" style="height: 200px;">
						
					</div>
				</div>
			</el-card>
		</el-col>
	</el-row>
	<el-row>
		<el-col :span="24">
			<el-card class="box-card">
				<div class="clearfix" slot="header">
					<span>业绩报表</span>
				</div>
				<div>
					
				</div>
			</el-card>
		</el-col>
	</el-row>

	<el-row :gutter="20">
		<el-col :span="12">
			<el-card class="box-card">
				<div class="clearfix" slot="header">
					<span>量化报表</span>
				</div>
				<div>
					
				</div>
			</el-card>
		</el-col>
		<el-col :span="12">
			<el-card class="box-card">
				<div class="clearfix" slot="header">
					<span>客户报表</span>
				</div>
				<div>
					
				</div>
			</el-card>
		</el-col>
	</el-row>
</div>
</div>


	<script src="/crm/Public/js/vue.js"></script>
	<script src="/crm/Public/js/element-ui--index.js"></script>
	<script src="/crm/Public/js/vue-resource.min.js"></script>
	
<script src="/crm/Public/js/echarts.min.js"></script>
	<script>
		window.vmHooks.add('mounted', function(){
			var myChart = echarts.init(document.getElementById('echarts'));
            myChart.setOption({
                // title: { text: 'ECharts 入门示例' },
                tooltip: {},
                xAxis: {
                    data: ["衬衫","羊毛衫","雪纺衫","裤子","高跟鞋","袜子"]
                },
                yAxis: {},
                series: [{
                    name: '销量',
                    type: 'bar',
                    data: [5, 20, 36, 10, 10, 20]
                }],
                grid:{
                	left:"50"
                }
            });
            window.onresize = myChart.resize;
		})
	</script>

	<script>
		window.vmInstance  = new Vue(window.vmOption);
	</script>
</body>
</html>