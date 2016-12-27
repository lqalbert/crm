// 表单的命名
var FormName = {
	type:"",
	getName:function(name){
		return name ? name : this.type;
	},
	getDialog:function(name){
		return this.getName(name) + "FormDialog";
	},
	getSubmitStatus:function(name){
		return this.getName(name) + "FormSubmitStatus";
	},
	getFormName:function(name){
		return this.getName(name) + "Form";
	}
};

/**
* 设置通用的逻辑
*
*/
function setCommonLogic(opt){
	opt.setDatas({
		show: false,

		dataList:[],
		dataLoad: false,
		
		total: 0,
		currentPage: 1,
		pageSize: page.pageSize,

		dialogLabelWidth:"140px",
		page:page

	});

	// 共用的 生命hook 处理函数
	opt.setVueHook("mounted", function(){
		this.show = true;
	})

	//触发 第一页事件
	opt.setVueHook("mounted", function(){
		this.$refs.pagination.$emit("current-change", 1);
	})

	//pagination
	// 共用的 方法 处理函数
	//重载 数据列表
	opt.setMethod("dataReload", function(){
		this.dataLoading = true;
		this.loadDatalist();
	})

	//根据 查询条件 重载数据
	opt.setMethod("loadDatalist", function(){
		var vmThis = this;
		this.$http.get(page.listUrl, {params: this.searchForm }).then(function(response){

			// 在显示之前 对数据进行处理
			if (this.beforeList) {
				vmThis.$set(vmThis, 'dataList', this.beforeList(response.body.list));
			} else {
				vmThis.$set(vmThis, 'dataList', response.body.list);
			}


			vmThis.$set(vmThis, 'total',    parseInt(response.body.count));
		}, function(response) {
			vmThis.$message({
			  message: '获取失败',
			  type: 'error'
			});
		}).finally(function(){
			vmThis.$set(vmThis, 'dataLoading', false);
		})
	})

	//处理翻页
	opt.setMethod("handleCurrentPageChange", function(v){
		this.dataLoading = true;
		this.searchForm.p = v;
		this.currentPage = v;
		this.loadDatalist();
	})

	//处理序号
	opt.setMethod("handleIndex", function(row, column){
		return this.dataList.indexOf(row)  + ((this.currentPage-1)*this.pageSize) +1;
	})

	// 初始化一个对像
	opt.setMethod("initObject", function(form, row){
		for(var x in form){
			form[x] = row[x];
		}
	})

	//查询清空
	opt.setMethod("searchReset", function(){
		// console.log("clear");
		this.$refs.searchForm.resetFields();
	})

	

	// //打开关闭对话框

	opt.setMethod("openDialog", function(dialog){
		this[FormName.getDialog(dialog)] = true;
	});

	opt.setMethod("closeDialog", function(dialog){
		this[FormName.getDialog(dialog)] = false;
	})

	/*
	* 添加 与 编辑
	*
	*/
	opt.setAdd();
	opt.setEdit();

}

/**
* 添加附加的选项
*/
function setAdvancedSearch(opt){

	var fieldName = FormName.getDialog("advancedSearch");;
	opt.setData(fieldName, false);

	
}

/**
* 设置 添加 与编辑
* @param VueOption
* @param string  add|edit
*/
function setForm(opt, type){
    FormName.type = type;
	var fieldName = FormName.getDialog();

	// addFormDialog editFormSubmit
	opt.setData(fieldName, false);
	// addFormSubmitStatus editFormSubmitStatus
	opt.setData(FormName.getSubmitStatus(), false);

	
	// 这里还是不好
	// 感觉还可以再拆分
	// 暂时先这样
	// todo form的命名 独立出来 
	opt.setMethod(type+"FormSubmit", function(url, form){
		FormName.type = form;

		var  vmThis   = this;
		var  formName    =  FormName.getFormName();       //form+"Form";
		var  formStatus  =  FormName.getSubmitStatus();  // formName+"FormSubmitStatus";
		var  formDialog  =  FormName.getDialog();         //form+"FormDialog";

		this[formStatus] =  true;

		this.$http.post(url, this[formName]).then(function (response) {
			vmThis.$message({
				  message: '添加成功',
				  type: 'success'
				});
			setTimeout(function(){
				vmThis[formDialog] = false;
				vmThis[formStatus] = false;
				vmThis.loadDatalist();
			}, 2000);
        }, function(response){
			vmThis.$message({
			  message: '添加失败',
			  type: 'error'
			});
        	vmThis[formStatus] = false;
        });
	})


}


