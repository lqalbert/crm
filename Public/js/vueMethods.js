//methods 虽然没有用比较新的技术 来实现，
//不过这样做也行
function VueMethods(){
	this.length =  0;
	this.keys   = [];
	this.data   = {};
}
VueMethods.prototype.set = function(key, value){
	if (this.data[key] == null) {
		this.keys.push(key);
		this.length = this.keys.length;
	}
	this.data[key] = value;
}

VueMethods.prototype.get = function(key){
	return this.data[key];
}

VueMethods.prototype.has = function(key){
	return this.get(key) ? true : false;
}

VueMethods.prototype.clear = function(){
	this.keys = [];
	// this.data = null;
	this.data = {};
	this.length = 0;
}
VueMethods.prototype.delete = function(key){
	this.keys.remove(key);
	this.data[key] = null;
	this.length = this.keys.length;
}

VueMethods.prototype.values = function(){
	return this.data;
}