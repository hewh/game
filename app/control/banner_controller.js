mboApp.controller("banner_ctrl",function($scope,$rootScope,$routeParams,apiUrl_service,user_service){
    $scope.logout=function(){
        var u = apiUrl_service.url_logout;
        user_service.logout(u,$rootScope);

    }
});
