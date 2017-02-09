//检测手机号码是否正确
//phone：手机号
//手机号码正确返回true,否则返回false
function isMobile(phone) {
    var phoneReg = /^1[2,3,4,5,6,7,8,9][0-9]{9}$/;//  /^1[34578]\d{9}$/
    if (phoneReg.test(phone)) {
        return true;
    }
    return false;
}

//邮件校验
//通过校验返回ture,否则返回false
function isEmail(emailStr) {
    if (emailStr.length == 0) {
        return fasle;      
    } else {
    var emailPat=/^(.+)@(.+)$/;
    var specialChars="/(/)<>@,;:///"/./[/]";
    var validChars="[^/s" + specialChars + "]";
    var quotedUser="("[^"]*")";
    var ipDomainPat=/^(d{1,3})[.](d{1,3})[.](d{1,3})[.](d{1,3})$/;
    var atom=validChars + '+';
    var word="(" + atom + "|" + quotedUser + ")";
    var userPat=new RegExp("^" + word + "(/." + word + ")*$");
    var domainPat=new RegExp("^" + atom + "(/." + atom + ")*$");
    var matchArray=emailStr.match(emailPat);
    if (matchArray == null) {
        return false;    
    }
    var user=matchArray[1];
    var domain=matchArray[2];
    if (user.match(userPat) == null) {
        return false;
    }
    var IPArray = domain.match(ipDomainPat);
    if (IPArray != null) {
        for (var i = 1; i <= 4; i++) {
           if (IPArray[i] > 255) {
              return false;
           }
        }
        return true;
    }
    var domainArray=domain.match(domainPat);
    if (domainArray == null) {
        return false;
    }
    var atomPat=new RegExp(atom,"g");
    var domArr=domain.match(atomPat);
    var len=domArr.length;
    if ((domArr[domArr.length-1].length < 2) ||
        (domArr[domArr.length-1].length > 3)) {
        return false;
    }
    if (len < 2) {
        return false;    
    }
    return true;
    }       
}

////IP地址校验 
//正确的IP地址回ture,否则返回false
function isIp(strIp) {
  var ipDomainPat=/^((2[0-4]d|25[0-5]|[01]?dd?).){3}(2[0-4]d|25[0-5]|[01]?dd?)$/;
  var matchArray=strIp.match(ipDomainPat);  
  if(matchArray!=null){
    return true;
  }               
}

//电话号码校验
//正确的电话号码（包括区号和“-”如0571-1234567[8] 010-1234567[8] ）则返回ture,否则返回false
function isMobilephoneNum(mobileNum){
      var mobilephoneNumPat=/^1d{10}|01d{10}$/;
      var matchArray=mobileNum.match(mobilephoneNumPat);
      if(matchArray!=null){
       return true;
       }
}

 //纯数字验证输入,输入为纯数字则返回ture,否则返回false
function isDigital(str){
     var digitalPot=/^d*$/;
     var matchArray=str.match(digitalPot);
     if(matchArray!=null){
      return true;
      }
}

// 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X 
function isCardNo(card)  {  
   var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;  
   if(reg.test(card) === false)  
   {  
       alert("身份证输入不合法");  
       return  false;  
   }  
}

//纯英文验证输入，判断是否为英文,正确返回ture,否则返回false
function isEnglish(name) { 
    if(name.length == 0)
     return false;
    for(i = 0; i < name.length; i++) { 
     if(name.charCodeAt(i) > 128)
      return false;
       }
   return true;
}

//纯中文验证输入，判断是否为中文，正确返回 ture，否则返回false
function isChinese(name) 
   { 
    if(name.length == 0)
     return false;
    for(i = 0; i < name.length; i++) { 
     if(name.charCodeAt(i) > 128)
      return true;
   }
    return false;
}

//非法字符判断,str中有charset则返回ture
function contain(str,charset){ 
    var i;
    for(i=0;i<charset.length;i++){
    if(str.indexOf(charset.charAt(i))>=0){
     return true;
    }
    return false;
    }
}

//选中文本框或文本域文本,在input位置加上 onClick/onFocus="textSelect();" 然后就可以使用以下代码    
function textSelect() {
    var obj = document.activeElement;
    if(obj.tagName == "TEXTAREA")
    {
      obj.select();
        }
       if(obj.tagName == "INPUT" )   {
            if(obj.type == "text")
        obj.select();
     }
}

//只允许输入字母，数字，下划线  
function textOnly(){
   var i= window.event.keyCode ;
   //8=backspace
   //9=tab
   //37=left arrow
   //39=right arrow
   //46=delete
   //48~57=0~9
   //97~122=a~z
   //65~90=A~Z
   //95=_
   if (!((i<=57 && i>=48)||(i>=97 && i<=122)||(i>=65 && i<=90)||(i==95)||(i==8)||(i==9)||(i==37)||(i==39)||(i==46))){
    //window.event.keyCode=27;
    event.returnValue=false;
    return false;
   } else {
    //window.event.keyCode=keycode;
    return true;
   }
}

//判断URL,正确的URL返回true,否则返回false   
function isURL() {
  var strRegex = "^((https|http|ftp|rtsp|mms)://)?[a-z0-9A-Z]{3}\.[a-z0-9A-Z][a-z0-9A-Z]{0,61}?[a-z0-9A-Z]\.com|net|cn|cc (:s[0-9]{1-4})?/$";
  var re = new RegExp(strRegex);
  if (re.test(document.getElementByIdx_x("<%=txtServerIP.ClientID %>").value)) {
    return true;
  } else {
    return false;
  }
}

//判断短日期(如2003-5-23)  
function isDate(date){
    var r = date.match(/^(d{1,4})(-|/)(d{1,2})(d{1,2})$/); 
    if(r==null){   
     return false; 
    }
    if (r[1]<1 || r[3]<1 || r[3]-1>12 || r[4]<1 || r[4]>31) {   
     return false
    }
    var d= new Date(r[1], r[3]-1, r[4]); 
    if(d.getFullYear()==r[1]&&(d.getMonth()+1)==r[3]&&d.getDate()==r[4]){
     return true;
    }
}

//判断短时间（HH:MM:SS）  
function isTime(time){
    var a = time.match(/^(d{1,2})(:)?(d{1,2})(d{1,2})$/);
    if (a == null) 
    {
     return false;
    }
    if (a[1]>23 || a[1]<0 || a[3]>60 || a[3]<0 || a[4]>60 || a[4]<0){
     return false
    }
    return true;
}

//判断字符最大长度,如果strin的长度不大于maxLen返回ture    
function maxLength(strin，maxLen) {   
    var len=0;
          for(var i=0;i<strin.length;i++)
    {
     if(strin.charCodeAt(i)>256)
     {
      len += 2;
     } else {
      len++;
     }
    }
    if(len<=maxLen){
     return true;
     } 
}

//判断字符最小长度,如果的长度不小于minLen返回ture  
function minLength(strin，minLen) {   
    var len=0;
    for(var i=0;i<strin.length;i++){
       if(strin.charCodeAt(i)>256){
        len += 2;
       } else {
        len++;
       }
    }
    if(len>=maxLen){
      return true;
    } 
}

 //由三个函数组成checkPassWord(),charMode(),bitTotal()
//校验密码复杂度,密码由数字,大小写字母,特殊字符中的任意三种组合,通过则返回true   
function checkPassWord(passWord,maxLen){  
    if (passWord.length<=maxLen)  
     return false; //密码太短  
    Modes=0;  
    for (i=0;i<passWord.length;i++){  
    //测试一个字符并判断一共有多少种模式.  
     Modes|=charMode(passWord.charCodeAt(i));  
    } 
    return bitTotal(Modes);  
}
//CharMode函数  
//判断某个字符是属于哪一种类型.  
function charMode(iN){  
  if (iN>=48 && iN <=57) //数字  
   return 1;  
  if (iN>=65 && iN <=90) //大写字母  
   return 2;  
  if (iN>=97 && iN <=122) //小写  
   return 4;  
  else  
   return 8; //特殊字符  
}  
//bitTotal函数  
//计算出当前密码当中一共有多少种模式  
function bitTotal(num){  
  modes=0;  
  for (i=0;i<4;i++){  
    if (num & 1) modes++;    
    num>>>=1;    
  }    
  if(modes==3){
    return true
  } 
}

//判断是否为合法的用户名，合法返回true,否则返回flase
//用户名由字母和数字、下划线组成，且只能以字母开头，且长度最小为6位
function isAccount(str){
  if(/^[a-z]w{3,}$/i.test(str)){
     return true;
  } else {
     return false;  
  }
}

//取得字符串中中文字的个数  
function getChineseNum(obstring){
    var pattern = /^[一-龥]+$/i;
    var maxL,minL;
    maxL = obstring.length;
    obstring = obstring.replace(pattern,"");
    minL = obstring.length;
    return (maxL - minL)
}