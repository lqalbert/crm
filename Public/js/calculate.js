  function showWeekFirstDay()     
  {     
      var Nowdate=new Date();     
      var WeekFirstDay=new Date(Nowdate-(Nowdate.getDay()-1)*86400000);     
      M=Number(WeekFirstDay.getMonth())+1   

      return WeekFirstDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(WeekFirstDay.getDate());     
  }

  function showWeekLastDay()     
  {     
    var Nowdate=new Date();     
    var WeekFirstDay=new Date(Nowdate-(Nowdate.getDay()-1)*86400000);     
    var WeekLastDay=new Date((WeekFirstDay/1000+6*86400)*1000);     
    M=Number(WeekLastDay.getMonth())+1     
    return WeekLastDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(WeekLastDay.getDate());     
  }

  function showMonthFirstDay()     
  {     
    var Nowdate=new Date();     
    var MonthFirstDay=new Date(Nowdate.getFullYear(),Nowdate.getMonth(),1);     
    M=Number(MonthFirstDay.getMonth())+1     
    return MonthFirstDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(MonthFirstDay.getDate());     
  }

  function showMonthLastDay()     
  {     
    var Nowdate=new Date();     
    var MonthNextFirstDay=new Date(Nowdate.getFullYear(),Nowdate.getMonth()+1,1);     
    var MonthLastDay=new Date(MonthNextFirstDay-86400000);     
    M=Number(MonthLastDay.getMonth())+1     
    return MonthLastDay.getFullYear()+"-"+formartNumber(M)+"-"+formartNumber(MonthLastDay.getDate());     
  }

  function formartNumber(v){
    if ((new String(v)).length==1) {
      return '0'+v;
    } else {
      return v;
    }
  }