function VueOption(){
	this.data     = {}; //;
	this.methods  = new VueMethods(); //new vueHooks();//{};

	this.hooks    = new VueHooks()//Object.create(VueHooks.prototype); //new VueHooks();
	this.computed = {}; //Object.create(Object.prototype); //{};
}

VueOption.prototype.setData = function(field, value) {
	this.data[field] = value;
};

VueOption.prototype.setDatas = function(obj) {
	Oassign(this.data, obj);
};

VueOption.prototype.setMethod = function(name, callback) {
	this.methods.set(name, callback);
};

VueOption.prototype.setVueHook = function(name, callback, fous) {
	this.hooks.add(name, callback, fous);
};

// 计算属性 可以设置getter setter
// 也可以 是 key : value 形式
/*VueOption.prototype.setComputed = function(name, callback) {
	// body...
};*/

VueOption.prototype.getOption = function() {
	var option = {};
	option['data'] = this.data;
	option['methods'] = this.methods.values();

	var thisOption = this;
	// Object.keys() 只会返回自有属性 
	// 从原型链上继承来的 不会 枚举
	// x in this.hooks 会把方法也枚举出来
	var keys = Object.keys(this.hooks).reverse();
	for (var i = keys.length - 1; i >= 0; i--) {
		var hookName = keys[i];
		if (this.hooks[hookName].length) {
			option[hookName] = function() {
				thisOption.hooks.exeHooks(this, hookName);
			}
		}  
	}

	return option;
};


// window.defaultOption = Object.create(VueOption.prototype);

// window.defaultVm = new Vue(window.defaultOption);