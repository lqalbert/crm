var showList = {};

  var trigger = document.getElementById("qqTrigger");


  function notify(option, customer){

    // option.icon="__PUBLIC__/images/0df431adcbef7609bca7d58a2adda3cc7cd99e73_r2_c2.jpg";

  
    if (false && window.Notification) {
        option.icon=imgurl + "/images/0df431adcbef7609bca7d58a2adda3cc7cd99e73_r2_c2.jpg"
        var n = new Notification("您有一个即将到点计划",option);
        n.addEventListener('click', showQQ);
        n.addEventListener('close', function(e){
            myWorker.postMessage({type:"del", data:this.customer.id});
            layui.jquery.post(setPlanUrl, {id:this.customer.id});
        });
    

        n.customer = customer;
    } else {
        if (!showList[customer.qq]) {
            $.amaran({
                'theme'     :'user no',
                'content'   :{
                    img: imgurl + '/images/0df431adcbef7609bca7d58a2adda3cc7cd99e73_r2_c2.jpg',
                    user:'您有一个即将到点计划',
                    message:option.body.replace("\n", "<br>")
                },
                'diyoption':  customer,
                'position'  :'bottom right',
                'outEffect' :'slideBottom',
                'sticky'    :true,
                onClick:function(){
                    if (!!this.diyoption.qq && this.diyoption.qq.length > 0) {
                        trigger.setAttribute('href', "tencent://message/?uin="+ this.diyoption.qq +"&Site=&menu=yes");
                        trigger.click();
                        
                    } else {
                        alert("客户："+this.diyoption.name+" 计划即将到点，没有设置QQ");
                    }
                    myWorker.postMessage({type:"del", data:this.diyoption.id});
                    layui.jquery.post(setPlanUrl, {id:this.diyoption.id});
                    delete showList[this.diyoption.id];
                    
                }
            });
            showList[customer.id] = customer;
        }
        
    }
    
    
  }

  function showQQ(){
    
    if (!!this.customer.qq && this.customer.qq.length > 0) {
        trigger.setAttribute('href', "tencent://message/?uin="+ this.customer.qq +"&Site=&menu=yes");
        trigger.click();
        
    } else {
        alert("客户："+this.customer.name+" 计划即将到点，没有设置QQ");
    }
    this.close();
  }

  // var de = {body:"客户：比",tag:"837737931"};
  // notify(de);

  var myWorker = new Worker(imgurl+'/js/worker.js');
  myWorker.onerror = function(e) { console.log(e); };
  myWorker.onmessage = function(e) { 
    // console.log(e);
    var d = new Date();
    d.setTime(e.data.time);
     notify({body:"客户："+e.data.name+"\n计划时间："+d.toLocaleString() + "\n" + e.data.remind, tag: e.data.id }, e.data);
  }

  // myWorker.addEventListener('message', function(e){
  //    console.log(e);
  //    notify({body:"客户："+e.data.name, tag: e.data.qq }, e.data);
  // })
  myWorker.postMessage({type:"start", data:dayPlanUrl});

  // 每过5分钟就重新获取
  setInterval(function(){
    myWorker.postMessage({type:"start", data:dayPlanUrl});
  }, 300000);
