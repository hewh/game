mboApp.controller("user_ctrl",function($scope,apiUrl_service,$http,user_service){
    $scope.checkNode=apiUrl_service.url_verify;
    $scope.getVerify= function(){
        $scope.checkNode=apiUrl_service.url_verify+"?"+Math.random();
    }
	$scope.login=function(){
		if($scope.formData.username!="" && $scope.formData.password!="")
			$http.post(apiUrl_service.url_login,$scope.formData).success(function(response){
				if(response.status==1)
					location.href="#/";
                else
                    alert(response.message);
			});
		else
			alert("请输入用户名或密码!!!");
	};
	$scope.reg=function(){
		if($scope.formData.username!="" && $scope.formData.password!="")
			if($scope.formData.password==$scope.pwdAgin)
				$http.post(apiUrl_service.url_register,$scope.formData).success(function(response){
					if(response.status==1)
						location.href="#/login";
					else
						alert(response.message);
				});
			else
				alert("两次密码不一样!!!");
		else
			alert("请输入用户名或密码!!!");
	};
});
