mboApp.controller("mark_ctrl",function($scope,$rootScope,$http,apiUrl_service,game_service,user_service){/*打分*/
	user_service.isLogin(apiUrl_service.url_islogin,$rootScope);
	//$scope.creatorClass = 'active-home';
	if($scope.mark_form == undefined){
		game_service.get_mark_form(apiUrl_service.url_getMarkForm,$scope);
		
	}
	$scope.setV = function(itemId) {
		var item = game_service.searchItem(itemId) ;
		item.get_score = "+"+item.score ;
	}
//	$scope.myCreator=function(){
//		$scope.creatorClass = 'active-home';
//		$scope.responsibleClass = '';
//		$scope.joinClass = '';
//		obj_service.home_myObj(apiUrl_service.url_getMyCreatorObj,$scope);
//	}
 
});
