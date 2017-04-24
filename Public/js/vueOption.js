function VueOption(){
	this.data     = {}; //;
	this.methods  = new VueMethods(); //new vueHooks();//{};

	this.hooks    = new VueHooks()//Object.create(VueHooks.prototype); //new VueHooks();
	this.computed = new VueMethods(); {}; //Object.create(Object.prototype); //{};
}

VueOption.prototype.setData = function(field, value) {
	this.data[field] = value;
	return this;
};

VueOption.prototype.setDatas = function(obj) {
	Oassign(this.data, obj);
	return this;
};

VueOption.prototype.setMethod = function(name, callback) {
	this.methods.set(name, callback);
	return this;
};

VueOption.prototype.setComputed = function(name, callback) {
	this.computed.set(name, callback);
	return this;
};

VueOption.prototype.setVueHook = function(name, callback, fous) {
	this.hooks.add(name, callback, fous);
	return this;
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
	option['computed'] = this.computed.values();

	var thisOption = this;


	option['beforeCreate'] = function() {
		thisOption.hooks.exeHooks(this, 'beforeCreate');
	}
	option['created'] = function() {
		thisOption.hooks.exeHooks(this, 'created');
	}
	option['beforeMount'] = function() {
		thisOption.hooks.exeHooks(this, 'beforeMount');
	}
	option['mounted'] = function() {
		thisOption.hooks.exeHooks(this, 'mounted');
	}
	option['beforeUpdate'] = function() {
		thisOption.hooks.exeHooks(this, 'beforeUpdate');
	}
	option['updated'] = function() {
		thisOption.hooks.exeHooks(this, 'updated');
	}
	option['activated'] = function() {
		thisOption.hooks.exeHooks(this, 'activated');
	}
	option['deactivated'] = function() {
		thisOption.hooks.exeHooks(this, 'deactivated');
	}
	option['beforeDestroy'] = function() {
		thisOption.hooks.exeHooks(this, 'beforeDestroy');
	}
	option['destroyed'] = function() {
		thisOption.hooks.exeHooks(this, 'destroyed');
	}

	// Object.keys() 只会返回自有属性 
	// 从原型链上继承来的 不会 枚举
	// x in this.hooks 会把方法也枚举出来

	var keys = Object.keys(this.hooks).reverse();
	for (var i = keys.length - 1; i >= 0; i--) {
		var hookName = keys[i];
		if (this.hooks[hookName].length == 0 ) {
			delete option[hookName];
		}  
	}

	return option;
};


//应用逻辑设置



//高级查询
VueOption.prototype.setAdvancedSearch = function(){
	setAdvancedSearch(this);
	return this;
}

//添加与编辑
VueOption.prototype.setAdd = function(){
	setForm(this, "add");
	return this;
}


VueOption.prototype.setEdit = function(){
	setForm(this, "edit");
	//初始化 
	this.setMethod("handleEdit", function(index, row, type){
		if (!arguments[2]) {
			type='edit';
		}
		this.initObject( this[FormName.getFormName(type)], row );
		this.editIndex = index;
		this.openDialog(type);
	})
	return this;
}


VueOption.prototype.setForm = function(name, obj){
	this.setData(FormName.getFormName(name), obj);
	if (arguments[2]) { // 是否设对应的表单 表单状态 提交的方法
		setForm(this, name); 
	}
	return this;
}


VueOption.prototype.setRowStyle = function() {
	this.setMethod('tableRowClassName', function(row, index){
		var i = index+1;
		if (i%4 === 2) {
          return 'info-row';
        } else if (i%4 === 0) {
          return 'positive-row';
        }
        return '';
	})
	return this;
}



// window.defaultOption = Object.create(VueOption.prototype);

// window.defaultVm = new Vue(window.defaultOption);