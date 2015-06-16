mboApp.filter("receive_status",function(){/*定义状态过滤器*/
    return function(input){
        if(input.toString()=="1")
            return true;
        else
            return false;
    };
});
mboApp.filter("time_format",function(){/*定义时间格式过滤器*/
    return function(input){
        var s=input;
        if(input.indexOf(":")>=0)
            s=input.substring(0,input.indexOf(' '));
        var iTime=new Date(s);
        var nTime=new Date();
        if(iTime.getYear()==nTime.getYear() && iTime.getMonth()==nTime.getMonth() && iTime.getDate()==nTime.getDate())
            if(input.indexOf(":")>=0)
                return "今天 "+input.substr(input.indexOf(' '));
            else
                return "今天";
        return input;
    }
});

mboApp.filter("between_time",function(){/*相差时间过滤器*/
    return function(input,status){
        var date,now,objectiveDate;
        //return input;
        date = new Date();
        now = date.toLocaleDateString();
        now = new Date(now);
        now = Math.floor(now.getTime()/1000/60/60/24);
        if(input == undefined || input == null || input == ''){
            return;
        }
        objectiveDate = input.replace("-","/").replace("-","/");
        objectiveDate = new Date(objectiveDate);
        objectiveDate = Math.floor(objectiveDate.getTime()/1000/60/60/24);
        return DateDiff(now,objectiveDate,status);
        function DateDiff(date1,date2,status){
            var diff;
            var rstr = "";
            diff = Math.abs(date1 - date2);
            if(status == 1){
                if(date1 > date2){
                    if(diff >= 2)
                        rstr = diff+"天前"
                    else if(diff == 1)
                        rstr = "昨天";
                    else
                        rstr = "今天";
                }else{
                    if(diff >= 3)
                        rstr = diff+"天后"
                    else if(diff == 2)
                        rstr = "后天";
                    else if(diff == 1)
                        rstr = "明天";
                    else
                        rstr = "今天";
                }

            }else if(status == 2){
                if(date1 > date2){
                    if(diff >= 2)
                        rstr = diff+"天前"
                    else if(diff == 1)
                        rstr = "昨天";
                    else
                        rstr = "今天";
                }else{
                    if(diff >= 3)
                        rstr = diff+"天后"
                    else if(diff == 2)
                        rstr = "后天";
                    else if(diff == 1)
                        rstr = "明天";
                    else
                        rstr = "今天";
                }
            }

            return rstr;
        }

    }
});
