// Object.assign 
if (typeof Object.assign != 'function') {
  (function () {
	Object.assign = function (target) {
	 'use strict';
	 if (target === undefined || target === null) {
	   throw new TypeError('Cannot convert undefined or null to object');
	 }
	
	 var output = Object(target);
	 for (var index = 1; index < arguments.length; index++) {
	   var source = arguments[index];
	   if (source !== undefined && source !== null) {
	     for (var nextKey in source) {
	       if (source.hasOwnProperty(nextKey)) {
	         output[nextKey] = source[nextKey];
	       }
	     }
	   }
	 }
	 return output;
	};
})();
}

/**
* 会修改 target
*/
function Oassign(target, source) {
	return Object.assign(target, source);
}
// Map 放在这以后用
if (!window.Map) {
	window.Map = function(){
		this.length = 0;
		this.keys = [];
		this.data = {};
	}

	window.Map.prototype.size = 0;
	window.Map.prototype.clear = function(){
		this.keys = [];
		// this.data = null;
		this.data = {};
		this.size = this.length = 0;
	}
	window.Map.prototype.delete = function(key){
		//这里不优雅 暂时这样搞
		// 应该把删除功能 放到 Array的原型上
		var i = this.keys.indexOf(key)
		if (i != -1) {
			this.keys.splice(i,1);
		}
		this.data[key] = null;
		this.size = this.length = this.keys.length;
	}
	window.Map.prototype.forEach = function(callback){
		for (var i = 0, len = this.length; i < len ; i++) {
			callback.call(this, this.keys[i], this.data[this.keys[i]], i);
		}
		
	}

	window.Map.prototype.get = function(key){
		return this.data[key];
	}

	window.Map.prototype.has = function(key){
		return this.get(key) ? true : false;
	}

	window.Map.prototype.keys = function(){
		return this.keys;
	}

	window.Map.prototype.set = function(key, value){
		if (this.data[key] == null) {
			this.keys.push(key);
			this.size = this.length = this.keys.length;
		}
		this.data[key] = value;
	}

	window.Map.prototype.values = function(){
		return null;
	}
}

// array remove
if (!Array.prototype.remove) {

	Array.prototype.remove = function(index){
		var i = this.indexOf(index)
		if (i != -1) {
			this.splice(i,1);
		}
	}
	
}