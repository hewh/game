/**
 * Created by 炼 on 2015/5/7.
 * angularJS 控制器
 */
homeApp.controller("myObj_ctrl",function($scope,$http){/*首页我的目标*/
    var getList=function(){
        $http.get($scope.myObjUrl).success(function(response){
            $scope.objList=response;
        });
    };
    getList();
    $scope.receive=function(id,url){
        url=url.substring(0,url.indexOf(".html"));
        url+="/oid/"+id;
        $http.get(url).success(function(response){
            if(response.status==1)
                getList();
            else
                alert(response.info);
        });
    };
});
homeApp.controller("todo_ctrl",function($scope,$http){/*首页to do list*/
    $http.get($scope.todoUrl).success(function(response){
        $scope.objList=response;
    });
});
homeApp.controller("dynamic_ctrl",function($scope,$http){/*首页最新动态*/
    $http.get($scope.dynamicUrl).success(function(response){
        $scope.objList=response;
    });
});