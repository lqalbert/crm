function phoneValidata(rule, value, callback) {
    var phoneReg = /^1[34578]\d{9}$/;

    if (null == value) {
      return callback();
    }

    if (value.length==0) {
      return callback();
    }

    if(!phoneReg.test(value)){
      return callback('手机号格式错误');
    }


    Vue.http.get(page.checContactUrl, {params:{value:value, type:'phone'}}).then(function(response){
      callback();
    }, function(response){
      callback('手机号已使用');
    });
}


function QQValidata(rule, value, callback){
    var QQReg = /^\d+$/;

    if (null == value) {
      return callback();
    }

    if (value.length==0) {
      return callback();
    }

    if(!QQReg.test(value)){
      return callback('QQ号必为数字');
    }

    Vue.http.get(page.checContactUrl, {params:{value:value, type:'qq'}}).then(function(response){
      callback();
    }, function(response){
      callback('QQ号已使用');
    });
}

function WxValidata(rule, value, callback){
    /*var reg = /^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}$/;*/
    if (null == value) {
      return callback();
    }
    if (value.length==0) {
      return callback();
    }

    /*if(!reg.test(value)){
      return callback('仅支持数字、下划线或减号，以字母开头');
    }*/

    Vue.http.get(page.checContactUrl, {params:{value:value, type:'weixin'}}).then(function(response){
      callback();
    }, function(response){
      callback('微信号已使用');
    });
}


