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
	},
	getForm:function(name){
		return this.getFormName(name);
	}
};

/**
* 设置通用的逻辑
*
*/
function setCommonLogic(opt){
	opt.setDatas({
		show: false,
		currentForm: null,

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
		
		this.dataLoad = true;
		this.loadDatalist();
		//window.location.reload();
	})

	//根据 查询条件 重载数据
	opt.setMethod("loadDatalist", function(){
		
		var vmThis = this;
		var params = {p:this.currentPage};
		for (var x in this.searchForm ){
			if (this.searchForm[x]!="") {
				params[x] = this.searchForm[x];
			}
		}
		this.$http.get(page.listUrl, {params:params}).then(function(response){

			// 在显示之前 对数据进行处理
			if (this.beforeList) {
				vmThis.$set(vmThis, 'dataList', this.beforeList(response.body.list));
			} else {
				vmThis.$set(vmThis, 'dataList', response.body.list);
			}
			vmThis.$set(vmThis, 'total',    parseInt(response.body.count));
		}, function(response) {
			vmThis.$message({
			  message: '获取数据失败：'+ response.body.info,
			  type: 'error'
			});
		}).finally(function(){
			vmThis.$set(vmThis, 'dataLoad', false);
		})
	})

	//处理翻页
	opt.setMethod("handleCurrentPageChange", function(v){
		this.dataLoad = true;
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
		this.dataLoad = true;
		this.loadDatalist();

		delete this.searchForm.sort_field;
    delete this.searchForm.sort_order;
		// window.location.reload();

	})

	

	// //打开关闭对话框

	opt.setMethod("openDialog", function(dialog){
		//console.log(this.multipleSelection);
		this[FormName.getDialog(dialog)] = true;
        //console.log(FormName.getDialog(dialog));
		this.currentForm = FormName.getForm(dialog);

	});

	opt.setMethod("closeDialog", function(dialog){
		this[FormName.getDialog(dialog)] = false;
		this.currentForm = null;
	})

	/*
	* 添加 与 编辑
	*
	*/
	opt.setAdd();
	opt.setEdit();

	//删除
	opt.setMethod("handleDelete", function(index, row, url){
		if (!arguments[2]) {
			url= this.page.deleteUrl;
		}
		var vmThis = this;
		this.$confirm('确定删除?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(function(){
        	vmThis.$http.post(url, {ids:[row.id]}).then(function(response){
        		var message = '删除成功!';
        		if (response.body.info) {
        			message = response.body.info;
        		}
        		vmThis.$message({
		            type: 'success',
		            message: message
		          });

        		// vmThis.dataList.splice(index, 1);

        		vmThis.loadDatalist();
        		//this.dataLoad = true;
		       // this.loadDatalist();

		      	}, function(response){
		      		var message = '删除失败';
		      		if (response.body.info) {
		      			message = response.body.info;
		      		}
		      		vmThis.$message({
			            type: 'error',
			            message: '删除失败'
			          });
		      })
        }).catch(function() {
          vmThis.$message({
            type: 'info',
            message: '已取消删除'
          });          
    });
	})

	opt.setMethod('getTenct', function(qq){
	    return "tencent://message/?uin="+ qq +"&Site=&menu=yes";
	})

	opt.setMethod('resetForm', function(type){
		this.$refs[FormName.getForm(type)].resetFields();
	})

	opt.setMethod('refresh', function(){
		window.location.reload();
	})


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
	
	opt.setMethod(type+"FormSubmit", function(url, form){
		FormName.type = form;
		var  vmThis   = this;
		var  formName    =  FormName.getFormName();       //form+"Form";
		try{
			if(!this.$refs[formName].rules){
				throw 'no rules';
			}

			this.$refs[formName].validate(function(valid){
				
				if (valid) {
					vmThis.commonSubmitLogic(url, form);
				} else {
					// console.log('error submit!!');
					console.log(vmThis[formName]);
					return false;
				}
			})

		}catch(e){
			vmThis.commonSubmitLogic(url, form);
		}
		/*if ( this.$refs[formName].rules ) {
			this.$refs[formName].validate(function(valid){
				if (valid) {
		            vmThis.commonSubmitLogic(url, form);
		          } else {
		            console.log('error submit!!');
		            return false;
		          }
			})
		} else {
			vmThis.commonSubmitLogic(url, form);
		}*/
	});

	opt.setMethod('commonSubmitLogic', function(url, form){
		FormName.type = form;
		var  vmThis   = this;
		var  formName    =  FormName.getFormName();       //form+"Form";
		var  formStatus  =  FormName.getSubmitStatus();  // formName+"FormSubmitStatus";
		var  formDialog  =  FormName.getDialog();         //form+"FormDialog";

		this[formStatus] =  true;
		this.$http.post(url, this[formName]).then(function (response) {

			vmThis.$message({
					  message: '操作成功',
					  type: 'success'
					});
			setTimeout(function(){
				vmThis[formDialog] = false;
				vmThis[formStatus] = false;
				vmThis.$refs[formName].resetFields();
				vmThis.dataReload();	
			}, 2000);
            
        }, function(response){	
        	vmThis.$message({
			  message: '操作失败：'+response.body.info,
			  type: 'error'
			});
        	setTimeout(function(){
        		vmThis[formStatus] = false;
        	},2000);
        });
	})




}

//设置重置表单
function setResetForm(opt, type){
	
	
}

//过滤器
Vue.filter("handleString", function(v) {

  if (v!=null && v.length > 3) {
  	var length = (arguments[1] || 3);
  	return v.substring(0,length)+'......';  
  } else {
  	return v;
  }
});
