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
	    *{
	    	font-family: microsoft YaHei;
	    }
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
			  <div style="text-align:center;">
			  	<img src="/crm/Public/images/0.jpg" class="image">

			  	<div>
			  		<p>姓名：张三丰</p>
			  		<p>ID：123456</p>
			  		<p>类型：网销主管</p>
			  		<p>QQ：123456</p>
			  		<p>电话：15845754875</p>
			  		<p>地址：湖北省十堰市 </p>
			  	</div>
			  </div>
			</el-card>
		</el-col>
		<el-col :span="16">
			<el-card class="box-card">
				<div slot="header" class="clearfix">
					系统公告
				</div>
				<div>
					<el-table
				      :data="sysData"
				      style="width: 100%">
				      <el-table-column
				        prop="sys_type"
				        label="类型"
				        width="180">
				      </el-table-column>
				      <el-table-column
				        prop="title"
				        label="标题">
				      </el-table-column>
				      <el-table-column
				        prop="date"
				        label="日期"
				        width="180">
				      </el-table-column>
				    </el-table>
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
					<el-table
				      :data="sysReport"
				      style="width: 100%">
				      <el-table-column
				        prop="date"
				        label="日期"
				        width="180">
				      </el-table-column>
						<el-table-column prop="zyj"   label="总业绩"></el-table-column>
						<el-table-column prop="cjc"   label="成交客户数（新成交/合作）"></el-table-column>
						<el-table-column prop="xfc"   label="续费客户数（续费/合作）"></el-table-column>
						<el-table-column prop="xfy"   label="续费业绩（续费/合作）"></el-table-column>
						<el-table-column prop="sjc"   label="升级客户数（升级/合作）"></el-table-column>
						<el-table-column prop="sjy"   label="升级业绩（升级）"></el-table-column>
				    </el-table>
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
					<el-table
						:data="data3"
						style="width:100%"
					>
						<el-table-column prop="date" label="日期"></el-table-column>
						<el-table-column prop="sd"   label="锁定"></el-table-column>
						<el-table-column prop="gz"   label="跟踪"></el-table-column>
						<el-table-column prop="tj"   label="转入"></el-table-column>
						<el-table-column prop="xg"   label="转出"></el-table-column>
						<el-table-column prop="xq"   label="冲突"></el-table-column>
					</el-table>
				</div>
			</el-card>
		</el-col>
		<el-col :span="12">
			<el-card class="box-card">
				<div class="clearfix" slot="header">
					<span>客户报表</span>
				</div>
				<div>
					<el-table
						:data="data3"
						style="width:100%"
					>
						<el-table-column prop="date" label="日期"></el-table-column>
						<el-table-column prop="sd"   label="锁定"></el-table-column>
						<el-table-column prop="gz"   label="跟踪"></el-table-column>
						<el-table-column prop="tj"   label="推介"></el-table-column>
						<el-table-column prop="xg"   label="修改"></el-table-column>
						<el-table-column prop="xq"   label="索取"></el-table-column>
					</el-table> 
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
                    data: ["2016-11","2016-12","2017-01","2017-02","2017-03","2017-04"]
                },
                yAxis: {},
                legend:{
                	show: true
                },
                series: [{
                    name: '销量',
                    type: 'bar',
                    data: [5, 20, 36, 10, 10, 20]
                },{
                    name: '业绩',
                    type: 'line',
                    data: [25, 21, 26, 90, 15, 50]
                }],
                grid:{
                	left:"50",
                	right:"50"
                }
            });
            window.onresize = myChart.resize;
		})

		Oassign(vmData, {
			sysData:[
				{
					sys_type:'功有升级',
					title:'新功能XXXX上线了',
					date:'2017-01-01 12:12'
				}
			],
			sysReport:[
				{
					date:'2016-12-01',
					zyj:'730',
					cjc:'0 / 0',
					xfc:'0.00 / 0.00',
					xfy:'1 / 0',
					sjc:'730.00 / 0.00',
					sjy:'0 / 0',
					
				},
				{
					date:'2016-12-01',
					zyj:'730',
					cjc:'0 / 0',
					xfc:'0.00 / 0.00',
					xfy:'1 / 0',
					sjc:'730.00 / 0.00',
					sjy:'0 / 0',
					
				}
				,
				{
					date:'2016-12-01',
					zyj:'730',
					cjc:'0 / 0',
					xfc:'0.00 / 0.00',
					xfy:'1 / 0',
					sjc:'730.00 / 0.00',
					sjy:'0 / 0',
					
				}
			],
			data3:[
				{
					date:'2016-01-01',
					sd:'1',
					gz:'2',
					tj:'0',
					xg:'4',
					xq:'1',
				},
				{
					date:'2016-01-01',
					sd:'1',
					gz:'2',
					tj:'0',
					xg:'4',
					xq:'1',
				},
				{
					date:'2016-01-01',
					sd:'1',
					gz:'2',
					tj:'0',
					xg:'4',
					xq:'1',
				},
				{
					date:'2016-01-01',
					sd:'1',
					gz:'2',
					tj:'0',
					xg:'4',
					xq:'1',
				}
			]
		})


	</script>

	<script>
		// 虽然有 if 但是 这样 methods 更灵活了
		// 可以兼容以的的代码
		if (window.vmMethods.length) {
			if (window.vmOption['methods']) {
				Oassign(window.vmOption['methods'],  window.vmMethods.values());
			} else {
				window.vmOption['methods'] = window.vmMethods.values();
			}
		}
		
		window.vmInstance  = new Vue(window.vmOption);
	</script>
</body>
</html>