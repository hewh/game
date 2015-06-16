var homeApp=angular.module("homeApp",[]); /*首页模块*/
homeApp.filter("receive_status",function(){/*定义状态过滤器*/
    return function(input){
        if(input.toString()=="1")
            return true;
        else
            return false;
    };
});
homeApp.filter("time_format",function(){/*定义时间格式过滤器*/
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
