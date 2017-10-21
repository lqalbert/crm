var max_size = 2*1024;
var slice = 0;//Math.ceil( / max_size);
var fileName = "";
var httpRequest = new XMLHttpRequest();
var file = null;
var cus_id = "";

function onreadyChange(){
    switch(httpRequest.readyState){
        case 0 : 
            // console.log(0,'未初始化....');
            break;
        case 1 : 
            // console.log(1,'请求参数已准备，尚未发送请求...');
            break;
        case 2 : 
            // console.log(2,'正在添加....');
            break;
        case 3 : 
            // console.log(3,'已接收数据长度：'+xhr.responseText.length );
            break;
        case 4 : 
            // console.log(4,'响应全部接受完毕');
            // console.log(httpRequest.response);
            var re = JSON.parse(httpRequest.response);//  eval("("++")");
            if ((httpRequest.status >= 200 && httpRequest.status < 300) || httpRequest.status == 304) {
                fileName = re.path;
                if (re.id) {
                    postMessage({type:'setRecord', record:re }); ;
                }
                // databox.innerHTML = httpRequest.responseText;
            }else{
                // databox.innerHTML = 'error:' + xhr.status;
                postMessage({type:'work', work:"error" }); ;
            }
            break;
    }
}


function init(data){
    file = data.file; // blob
    max_size = data.max_size;
    slice = Math.ceil( file.size / max_size);
    cus_id = data.cus_id;
}


function start(url){
   
    httpRequest.onreadystatechange = onreadyChange;
    httpRequest.ontimeout = function(e) { 
        console.log("超时了");
    };
    httpRequest.onerror = function(e) {
        console.log("出错了");
    };
    httpRequest.onprogress = function(e) { 
        console.log("传输中");
    };
    // console.log(file);
    for(var i=0; i< slice; i++){
      var formData = new FormData();
      formData.append('cus_id', cus_id);
      // new File(bits, name[, options]);
      formData.append('filename', file.name);
      formData.append('file', file.slice(i*max_size, (i+1) * max_size)); 
      // formData.append('file', file.slice(i*max_size, (i+1) * max_size, file.type)); 
      formData.append('index', i); 
      formData.append('path', fileName); 

      httpRequest.open('POST', url, false);
      httpRequest.send(formData);

      postMessage({type:'progress', progress: parseInt((i+1) / slice * 100)}); ;

      formData = null;
      b =null;

    }
    file = null
    
}

self.onmessage = function (msg) {
    
    switch (msg.data.type) {
        case 'init':
            init(msg.data);
            break;
        case 'start':
            start(msg.data.url);
            break;
        default:
            console.log(msg);
    }

    //remove someOne item

    
    // remove();
}
