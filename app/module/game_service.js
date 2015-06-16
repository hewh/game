/*定义路由*/
mboApp.config(['$routeProvider',function($routeProvider){
	$routeProvider.when('/',{
		templateUrl:'view/index.html'
	}).when('/mark',{
		templateUrl:'view/mark.html'
	}).when('/login',{
		templateUrl:'view/login.html'
	}).when('/reg',{
		templateUrl:'view/regiest.html'
	})
	;
}]);
mboApp.factory('game_service',function($http){
	return {
		date:[1,2,3],
		get_mark_form:function(ajaxUrl,v){/*获取打分列表*/
			_this=this ;
			$http.get(ajaxUrl).success(function(response){
				v.mark_form=response.itemType;
				_this.data=v.mark_form ;
			});
		},
		searchItem: function(itemId){
			var mark_form = this.data ;
			for(var o in mark_form) {
				for(var oi in mark_form[o].items){
					var item = mark_form[o].items[oi] ;
					if ( item.id == itemId)
						return item ;
				}	
			}
			return null ;
		},
		creatObj:function(ajaxUrl,postData,v){/*创建目标*/
			$http.post(ajaxUrl,postData).success(function(response){
				if(response.status.toString()=="1"){
					v.cancelNew();	
				}else
					alert(response.data);	
			});			
		},
		delete:function(ajaxUrl,n){
			$http.get(ajaxUrl).success(function(response){
				if(response.status != 1){
					alert(response.message);
				}
				return true;
			});

		}
	};
});

mboApp.factory('user_service', function ($http) {
    return {
        getVerify:function(ajaxUrl,v){
            $http.get(ajaxUrl).success(function (response) {
                v.checkNode=response;
            });
        },
		isLogin:function(ajaxUrl,v){
			$http.get(ajaxUrl).success(function(response){
				if(!response.status)
					location.href="#/login";
				else{
					v.loginUid = response.uid;
					v.username = response.username;
				}


			});
		},
		logout:function(ajaxUrl,v){
			$http.get(ajaxUrl).success(function(response){
				if(response.status){
					v.loginUid = null;
					v.username = null;
					alert(response.message);
					location.href="#/login";
				}else{
					alert(response.message);
					return false;
				}


			});
		}

    };
});
mboApp.run(function(user_service,apiUrl_service){
	//user_service.isLogin(apiUrl_service.url_islogin);
});
