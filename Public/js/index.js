/** index.js By Beginner Emain:zheng_jinfan@126.com HomePage:http://www.zhengjinfan.cn */
layui.config({
	base: window.webRoot+'Public/js/'
}).use(['element', 'layer', 'navbar', 'tab'], function() {
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
        


	   $('#lock').mouseover(function(){
	   	   layer.tips('请按Alt+L快速锁屏！', '#lock', {
	             tips: [1, '#FF5722'],
	             time: 4000
	       });
	   })
	   // 快捷键锁屏设置
	    $(document).keydown(function(e){
	         if(e.altKey && e.which == 76){
	         	 lockSystem();
	         }else if(e.which == 116){
	         	 lockSystem();
	         }else if(e.ctrlKey && e.which == 82){
                 lockSystem();
	         }else if(e.ctrlKey && e.which == 82){

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
	        	$('.lock-screen').show(0,sb);
	            $('#locker').show(0,sb);
	            $('#layui_layout').hide(0,sb);
	            //$('#lock_password').val('');
	        }else{
	        	$('.lock-screen').hide();
	            $('#locker').hide();
	            $('#layui_layout').show();
	        }
	    }
       function sb(){
       	 $(document).keydown(function(e){
	         if(e.altKey && e.which == 76){
	         	 checkLockStatus(1);
	         }else if(e.which == 116){
	         	 checkLockStatus(1);
	         }else if(e.ctrlKey && e.which == 82){
                 checkLockStatus(1);
	         }else if(e.ctrlKey && e.which == 82){
                 checkLockStatus(1);
	         }
	    });
       
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
		          //checkLockStatus(1)
		         }else{
		           checkLockStatus(1);  
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
});