var checkIdNum=function(rule,value,callback){
    var number=/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
    if(value==='' || value===null){
      callback();
    }else if(!number.test(value)){
      callback('身份证号格式错误');
    }else{
      callback();
    }
}

var  editCheckPhone = function(rule,value,callback){
    if (window.defaultVm.editOld.phone2 != value) {
      phoneValidata(rule,value,callback);
    } else {
      callback();
    }
}

var  editCheckQq = function(rule,value,callback){
    if (window.defaultVm.editOld.qq != value) {
      QQValidata(rule,value,callback);
    } else {
      callback();
    }
}

var  editCheckQq2 = function(rule,value,callback){
    if (window.defaultVm.editOld.qq2 != value) {
      QQValidata(rule,value,callback);
    } else {
      callback();
    }
  }



  var  editCheckWeixin = function(rule,value,callback){

    if (window.defaultVm.editOld.weixin != value) {
      WxValidata(rule,value,callback);
    } else {
      callback();
    }
  }

  var  editCheckWeixin2 = function(rule,value,callback){
    if (window.defaultVm.editOld.weixin2 != value) {
      WxValidata(rule,value,callback);
    } else {
      callback();
    }
  }


   var CustomerRule = {
      name:[
        { required: true, message: '请输入客户姓名', trigger: 'blur' },
      ],
      phone:[
        { required: true, message: '请输入客户手机', trigger: 'blur' },
        { validator: phoneValidata , trigger: 'blur' },
      ],
      phone2:[
        { validator: phoneValidata, trigger: 'blur' }
      ],
      qq:[
        { pattern:/^\d+$/, message: 'QQ号必须为数字'},
        { validator: QQValidata, trigger:'blur' },
      ],
      qq2:[
        { pattern:/^\d+$/, message: 'QQ号必须为数字'},
        { validator: QQValidata, trigger:'blur' },
      ],
      weixin:[
          { min: 6, max: 20, trigger:'blur', message:"长度为6-20个字符" },
          { validator: WxValidata, trigger:'blur' },
      ],
      weixin2:[
          {min: 6, max: 20, trigger:'blur', message:"长度为6-20个字符" },
          { validator: WxValidata, trigger:'blur' },
      ],

      type:[
        { required:true, message:'请选择客户类型',trigger:'change'},
      ],
      money:[
        { required:true, message:'请选择客户资料量',trigger:'change'},
      ],
      source:[
        { required:true, message:'请选择客户来源', trigger:'change'},
      ]
    };


     var CustomerEditRule = {
      name:[
        { required: true, message: '请输入客户姓名', trigger: 'blur' },
      ],
      phone:[
        { required: true, message: '请输入客户手机', trigger: 'blur' },
        { validator: editCheckPhone , trigger: 'blur' },
      ],
      phone2:[
        { validator: editCheckPhone, trigger: 'blur' }
      ],
      qq:[
        { pattern:/^\d+$/, message: 'QQ号必须为数字'},
        { validator: editCheckQq, trigger:'blur' },
      ],
      qq2:[
        { pattern:/^\d+$/, message: 'QQ号必须为数字'},
        { validator: editCheckQq2, trigger:'blur' },
      ],
      weixin:[
          { min: 6, max: 20, trigger:'blur', message:"长度为6-20个字符" },
          { validator: editCheckWeixin, trigger:'blur' },
      ],
      weixin2:[
          {min: 6, max: 20, trigger:'blur', message:"长度为6-20个字符" },
          { validator: editCheckWeixin2, trigger:'blur' },
      ],

      type:[
        { required:true, message:'请选择客户类型',trigger:'change'},
      ],
      money:[
        { required:true, message:'请选择客户资料量',trigger:'change'},
      ],
      source:[
        { required:true, message:'请选择客户来源', trigger:'change'},
      ]
    };