// 存储 生命周期 处理函数的数组
// 添加 删除某一个 移除所有
window.VUE_HOOKS_NAME = [
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

function VueHooks(){
	window.VUE_HOOKS_NAME.forEach(function(value){
		this[value] = [];
	},this);
}


VueHooks.prototype.add = function(hookName, callback, fous){
	if (this[hookName].indexOf(callback) != -1  ) {
		if (fous) {
			this.del(callback);
			this[hookName].push(callback);
		}
		// else 什么都不做
	} else {
		this[hookName].push(callback);
	}
}

VueHooks.prototype.del = function(hookName, callback){
	if (callback) {
		var index = this[hookName].indexOf(callback);
		if (index != -1) {
			this[hookName].splice(index , 1);
		}
	} else {
		this.delAll(hookName);
	}
}

VueHooks.prototype.delAll = function(hookName){
	this[hookName] = [];
}

VueHooks.prototype.exeHooks = function(vmThis, hookName){
	this[hookName].forEach(function(currentValue){
		currentValue.call(this);
	}, vmThis)
}

/*window.vmHooks = new hooks();

window.vmHooks.add("mounted", function(){
	this.show = true;
})*/