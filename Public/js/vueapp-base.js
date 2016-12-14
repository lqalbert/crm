// 存储 生命周期 处理函数的数组
// 添加 删除某一个 移除所有
window.HOOKSNAME = [
	'beforeCreate',
	'created',
	'beforeMount',
	'mounted',
	'beforeUpdate',
	'updated',
	'activated',
	'deactivated',
	'beforeDestroy',
	'destroyed'
];

function hooks(){
	window.HOOKSNAME.forEach(function(value){
		this[value] = [];
	},this);
}


hooks.prototype.add = function(hookName, callback, fous){
	if (this[hookName].indexOf(callback) != -1  ) {
		if (fous) {
			this[hookName].push(callback);
		}
		// else 什么都不做
	} else {
		this[hookName].push(callback);
	}
}

hooks.prototype.del = function(){
	if (callback) {
		var index = this[hookName].indexOf(callback);
		if (index != -1) {
			this[hookName].splice(index , 1);
		}
	} else {
		this.delAll(hookName);
	}
}

hooks.prototype.delAll = function(){
	this[hookName] = [];
}

hooks.prototype.exeHooks = function(vmThis, hookName){
	window.vmHooks['mounted'].forEach(function(currentValue){
		currentValue.call(this);
	}, vmThis)
}

window.vmHooks = new hooks();

window.vmHooks.add("mounted", function(){
	
	this.show = true;
})



// console.log(window.vmHooks); 

// show false 在 mounted 之前不起作用
// #app>wrapp inline style display:none 
// 在mounted之前 v-show 不起作用 , #app>wrapp 不会因为show 为 false
// 而隐藏 ,为了隐藏 #app>wrapp 需要 inline style display:none,
// 而后利用 mounted 钩子 使 this.show = true, 促使 vue 更改 inline style
// 的 display 属性值。 完美的解决了 打开页面在渲染之前 没有样式的问题 
// 有个小问题 display:none 必须是内联 写在头部 就不会显示了
// v-show 为 false 时会 display:none ；true 时 会移除 display;
window.vmData = {
	show:false
};

window.vmOption = {
	el:'#app',
	data: window.vmData,
	beforeCreate:function(){
		window.vmHooks.exeHooks(this, 'beforeCreate');
	},
	created: function(){
		window.vmHooks.exeHooks(this, 'created');
	},
	beforeMount: function(){
		window.vmHooks.exeHooks(this, 'beforeMount');
	},
	mounted: function(){
		window.vmHooks.exeHooks(this, 'mounted');
	},
	beforeUpdate: function(){
		window.vmHooks.exeHooks(this, 'beforeUpdate');
	},
	updated: function(){
		window.vmHooks.exeHooks(this, 'updated');
	},
	activated: function(){
		window.vmHooks.exeHooks(this, 'activated');
	},
	deactivated: function(){
		window.vmHooks.exeHooks(this, 'deactivated');
	},
	beforeDestroy: function(){
		window.vmHooks.exeHooks(this, 'beforeDestroy');
	},
	destroyed: function(){
		window.vmHooks.exeHooks(this, 'destroyed');
	}
	
}