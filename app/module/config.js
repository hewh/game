var mboApp=angular.module("mboApp",['ngRoute']);
/*ajax路径配置服务*/
mboApp.factory('apiUrl_service',function(){
	return {
		url_getMarkForm:api_Path+"Score/getMarkForm" ,
	
		url_login:api_Path+"User/login",
		url_logout:api_Path+"User/logout",
		url_islogin:api_Path+"User/islogin",
		url_verify:api_Path+"User/verify"
	};
});