  //本周第一天
  function showWeekFirstDay(){     
    var Nowdate=new Date();     
    var WeekFirstDay=new Date(Nowdate-(Nowdate.getDay()-1)*86400000);     
    M=Number(WeekFirstDay.getMonth())+1   
    return WeekFirstDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(WeekFirstDay.getDate());     
  }
  
  //本周最后一天
  function showWeekLastDay(){     
    var Nowdate=new Date();     
    var WeekFirstDay=new Date(Nowdate-(Nowdate.getDay()-1)*86400000);     
    var WeekLastDay=new Date((WeekFirstDay/1000+6*86400)*1000);     
    M=Number(WeekLastDay.getMonth())+1     
    return WeekLastDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(WeekLastDay.getDate());     
  }
  

  //上周第一天
  function showLastWeekFirstDay(){
    var Nowdate=new Date();     
    var LastWeekFirstDay=new Date(Nowdate-(Nowdate.getDay()+6)*86400000);     
    M=Number(LastWeekFirstDay.getMonth())+1   
    return LastWeekFirstDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(LastWeekFirstDay.getDate()); 
  }

  //上周最后一天
  function showLastWeekLastDay(){
    var Nowdate=new Date();     
    var LastWeekFirstDay=new Date(Nowdate-(Nowdate.getDay()+6)*86400000);
    var LastWeekLastDay=new Date((LastWeekFirstDay/1000+6*86400)*1000); 
    M=Number(LastWeekLastDay.getMonth())+1   
    return LastWeekLastDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(LastWeekLastDay.getDate()); 
  }


  //本月第一天
  function showMonthFirstDay(){     
    var Nowdate=new Date();     
    var MonthFirstDay=new Date(Nowdate.getFullYear(),Nowdate.getMonth(),1);     
    M=Number(MonthFirstDay.getMonth())+1     
    return MonthFirstDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(MonthFirstDay.getDate());     
  }

  //本月最后一天
  function showMonthLastDay(){     
    var Nowdate=new Date();     
    var MonthNextFirstDay=new Date(Nowdate.getFullYear(),Nowdate.getMonth()+1,1);     
    var MonthLastDay=new Date(MonthNextFirstDay-86400000);     
    M=Number(MonthLastDay.getMonth())+1     
    return MonthLastDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(MonthLastDay.getDate());     
  }
  
  //上月第一天
  function showLastMonthFirstDay(){
    var Nowdate=new Date();     
    var LastMonthFirstDay=new Date(Nowdate.getFullYear(),Nowdate.getMonth()-1,1);     
    M=Number(LastMonthFirstDay.getMonth())+1     
    return LastMonthFirstDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(LastMonthFirstDay.getDate());     
  }
  //上月最后一天
  function showLastMonthLastDay(){
    var Nowdate=new Date();     
    var MonthFirstDay=new Date(Nowdate.getFullYear(),Nowdate.getMonth(),1);   
    var LastMonthLastDay=new Date(MonthFirstDay-86400000); 
    M=Number(LastMonthLastDay.getMonth())+1 
    return LastMonthLastDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(LastMonthLastDay.getDate());    
  }

  function formartNumber(v){
    if ((new String(v)).length==1) {
      return '0'+v;
    } else {
      return v;
    }
  }