

var cusList = [
    // {time:new Date(), qq:"837737931", name:"张某"}
];

var interval = null;

// clearInterval(interval);

function remove(x){
    console.log("remove function");
    console.log(x);
    for (var i = 0, len = cusList.length; i < len ; i++) {
        if (cusList[i].qq == x) {
            cusList.splice(i, 1);
            break;
        }
    }
    
}

function start(url){
    console.log("start");
    var variable=new XMLHttpRequest();
    variable.open("GET", url);
    variable.onload = function(e) { 
        console.log("onload", this.status);
        if(this.status == 200||this.status == 304){
            // alert(this.responseText);
            console.log(this.response);
            cusList = JSON.parse(this.responseText) ;

            if (cusList.length > 0) {
                interval = setInterval(function(){
                    var d = new Date() ;
                    
                    // console.log(d);
                    // 这里时间 有问題
                    for (var i = 0, len = cusList.length; i < len ; i++) {
                        // console.log(cusList[i]);
                        var b = new Date();
                        b.setTime(cusList[i].time);
                        b.setMinutes(b.getMinutes() - 5);
                        if (b <= d) {

                            postMessage(cusList[i]);
                        }
                    }
                }, 1000);
            }
            

        }
    };
    // variable.responseType = "json";
    variable.ontimeout = function(e) { 
        console.log("超时了");
    };
    variable.onerror = function(e) {
        console.log("出错了");
    };
    variable.onprogress = function(e) { 
        console.log("传输中");
    };
    variable.send();
}

self.onmessage = function (msg) {
     console.log(msg);
    switch (msg.data.type) {
        case 'start':
            start(msg.data.data);
            break;
        case 'del':
            remove(msg.data.data);
            break;
        default:
            console.log(msg);
    }

    //remove someOne item

    console.log(msg);
    // remove();
}
