/** index.js By Beginner Emain:zheng_jinfan@126.com HomePage:http://www.zhengjinfan.cn */
layui.config({
	base: window.webRoot+'Public/js/'
}).use(['element', 'layer', 'navbar', 'tab','jquery'], function() {
	var element = layui.element(),
		$ = layui.jquery,
		layer = layui.layer,
		navbar = layui.navbar(),
		tab = layui.tab({
			elem: '.admin-nav-card' //设置选项卡容器
		});
	//iframe自适应
	$(window).on('resize', function() {
		var $content = $('.admin-nav-card .layui-tab-content');
		$content.height($(this).height() - 103);
		$content.find('iframe').each(function() {
			$(this).height($content.height());
		});
	}).resize();

    //可关闭选项卡
	element.on('nav(user)', function(data) {
		var $a = data.children('a');
		if($a.data('tab') !== undefined && $a.data('tab')) {
			tab.tabAdd({
				title: $a.children('cite').text(),
				icon: $a.children('i').text(),
				href: $a.data('url')
			});
		}
	});

	//设置navbar
	navbar.set({
		spreadOne: true,//设置只打开一个导航开启
		cached:true,//设置缓存开启
		elem: '#admin-navbar-side',
		data: navs
			//url: 'datas/nav.json'
	});

	//渲染navbar
	navbar.render();
	//监听点击事件
	navbar.on('click(side)', function(data) {
		tab.tabAdd(data.field);
	});

	$('.admin-side-toggle').on('click', function() {
		var sideWidth = $('#admin-side').width();
		if(sideWidth === 200) {
			$('#admin-body').animate({
				left: '0'
			}); //admin-footer
			$('#admin-footer').animate({
				left: '0'
			});
			$('#admin-side').animate({
				width: '0'
			});
		} else {
			$('#admin-body').animate({
				left: '200px'
			});
			$('#admin-footer').animate({
				left: '200px'
			});
			$('#admin-side').animate({
				width: '200px'
			});
		}
	});

	//手机设备的简单适配
	var treeMobile = $('.site-tree-mobile'),
		shadeMobile = $('.site-mobile-shade');
	treeMobile.on('click', function() {
		$('body').addClass('site-mobile');
	});
	shadeMobile.on('click', function() {
		$('body').removeClass('site-mobile');
	});
    
    //屏幕解锁
	$(function(){
	   //锁屏提示       
	   $('#lock').mouseover(function(){
	   	   layer.tips('请按Alt+L快速锁屏！', '#lock', {
	             tips: [1, '#FF5722'],
	             time: 1000
	       });
	   });
	   // 快捷键锁屏设置
	    $(document).keydown(function(e){
	         if(e.altKey && e.which == 76){
	         	 lockSystem();
	         }
	    });
	   function startTimer(){
	   	    var today=new Date();
	        var h=today.getHours();
	        var m=today.getMinutes();
	        var s=today.getSeconds();
	        m = m < 10 ? '0' + m : m;
	        s = s < 10 ? '0' + s : s;
	        $('#time').html(h+":"+m+":"+s);
	        var t=setTimeout(function(){startTimer()},500);
	   }
	   // 锁屏状态检测
	   function checkLockStatus(locked){
	        // 锁屏
	        if(locked == 1){
	        	$('.lock-screen').show(0,fordidden);
	            $('#locker').show(0,fordidden);
	            $('#layui_layout').hide(0,fordidden);
	            //$('#lock_password').val('');
	        }else{
	        	$('.lock-screen').hide(1,allow);
	            $('#locker').hide(1,allow);
	            $('#layui_layout').show(1,allow);
	        }
	    }

	   //锁屏状态下禁止刷新
       function fordidden(){
       	 $(window).keydown(function(e){
	         if(e.altKey && e.which == 76){
	         	 return false;
	         	// checkLockStatus(1);
	         }else if(e.which == 116){ //F5
	         	 return false;
	         	 //checkLockStatus(1);
	         }else if(e.ctrlKey && e.which == 82){ // ctrl+R
	         	 return false;
                 //checkLockStatus(1);
	         }else if(e.ctrlKey && e.which == 116){ //ctrl + F5
                 return false;
                 //checkLockStatus(1);
	         }else if(e.which == 13){
	         	 return false;
	         }
	     }).bind("contextmenu",function(e){ //禁止右击
             return false; 
	     });
	     //console.log(window.location.href);
	     // function RunOnBeforeUnload() {
	     // 	window.onbeforeunload = function(){ 
	     // 		return '将丢失未保存的数据!';
	     // 	} 
	     // }
       }

       //解锁完毕以后开启刷新
       function allow(){

       	 $(window).keydown(function(e){
	         if(e.altKey && e.which == 76){
	         	 return true;
	         	// checkLockStatus(1);
	         }else if(e.which == 116){ //F5
	         	 return true;
	         	 //checkLockStatus(1);
	         }else if(e.ctrlKey && e.which == 82){ // ctrl+R
	         	 return true;
                 //checkLockStatus(1);
	         }else if(e.ctrlKey && e.which == 116){ //ctrl + F5
                 return true;
                 //checkLockStatus(1);
	         }else if(e.which == 13){
	         	 return true;
	         }
	     }).bind("contextmenu",function(e){
             return true; 
	     });
	     //window.onload();   
       }
         

	   checkLockStatus('0');
	   // 锁定屏幕
	   function lockSystem(){
	   		
	   	   var url = indexUrl+'/lock';
	   	   $.post(url,function(data){
	   	   	   if(data=='1'){
	   	   	   	  checkLockStatus(1);
	   	   	   }else{
	              layer.alert('锁屏失败，请稍后再试！');
	   	   	   }
	   	   });
	   	   startTimer();
	   }
	   //解锁屏幕
	   function unlockSystem(){
	        // 与后台交互代码已移除，根据需求定义或删除此功能
	   	    checkLockStatus(0);
	    }
	   // 点击锁屏
	   $('#lock').click(function(){
	   	    lockSystem();
	   });
	   // 解锁进入系统
	   $('#unlock').click(function(val){
	   	    var lockpwd=$('#lock_password').val();
		     $.ajax({
		       type:"post",
		       url:indexUrl+'/checkLock',
		       data:'lockpwd='+lockpwd,//传值给服务器端
		       success:function(response){
		         if(response==1){
		          unlockSystem();
		          allow();
		          window.location.reload();
		          //checkLockStatus(1)
		         }else{
		           //checkLockStatus(1);  
		           layer.open({
		           	 title:'警告',
		           	 content:'密码错误！别想偷看，死鬼！',
		           	 shadeClose:true,
		           	 time:3000,
		           });
		         }
		       }

		     });
	        
	   });
	   // 监控lock_password 键盘事件
	   $('#lock_password').keypress(function(e){
	        var key = e.which;
	        if (key == 13) {
	            unlockSystem();
	        }
	    });
	    
	});
    //刷新提示语句
    $('#refresh').mouseover(function(){
   	    layer.tips('请点击我刷新！', '#refresh', {
             tips: [1, '#FF5722'],
             time: 1000
        });
    });

    //点击刷新
    $('#refresh').click(function(){
    	window.location.reload();
    });

    
});
//获取当前时间
function getCurDate() {
	  var d = new Date();
	  var week;
	  switch (d.getDay()) {
	    case 1:
	      week = "星期一";
	      break;
	    case 2:
	      week = "星期二";
	      break;
	    case 3:
	      week = "星期三";
	      break;
	    case 4:
	      week = "星期四";
	      break;
	    case 5:
	      week = "星期五";
	      break;
	    case 6:
	      week = "星期六";
	      break;
	    default:
	      week = "星期天";
	  }
	  var years = d.getFullYear();
	  var month = add_zero(d.getMonth() + 1);
	  var days = add_zero(d.getDate());
	  var hours = add_zero(d.getHours());
	  var minutes = add_zero(d.getMinutes());
	  var seconds = add_zero(d.getSeconds());
	  var ndate = years + "年" + month + "月" + days + "日 " + hours + ":" + minutes + ":" + seconds + " " + week;
	  $(".date").html(ndate);
}

function add_zero(temp) {
   if (temp < 10) return "0" + temp;
   else return temp;
}
setInterval("getCurDate()", 100);