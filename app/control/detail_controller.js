mboApp.controller("objDetail_ctrl",function($scope,$rootScope,$routeParams,apiUrl_service,obj_service,task_service,user_service){
	user_service.isLogin(apiUrl_service.url_islogin,$rootScope);

	$scope.tabClass_task={doTask:"active-taskTab",findshTask:""};
	$scope.status = 0;
    $scope.oid = $routeParams.oid;

    $scope.isTree=false;
    $scope.isNew=false;
    $scope.isEdit=false;
    $scope.isTNew=false;
    $scope.nObj={title:"",description:"",creator:"1",join:"",important:"",deadline:"",joinName:"",responsibleName:"",fid:$scope.oid};
    $scope.path = apiUrl_service.url_objDetail +"/oid/"+$scope.oid;
    obj_service.getMyObjs($scope.path,$scope,0);
    $scope.path = apiUrl_service.url_childObjective +"/oid/"+$scope.oid;
    obj_service.getMyObjs($scope.path,$scope,1);
    $scope.path = apiUrl_service.url_objTask +"/oid/"+$scope.oid;
    obj_service.getMyObjs($scope.path,$scope,2);
    $scope.openNewO=function(){
		//$scope.openSelectC(0);
		$scope.openSelectR(0);
		$scope.openSelectJ(0);
		$("#tbJoin").html("");
    	$scope.cancelNew();
    	$scope.isNew=true;
    	$scope.isTNew=false;
    };
    $scope.cancelNew=function(){
    	$scope.isNew=false;
    	$scope.isEdit=false;
    	$(".obj-fotmBtn:first").show();
		$(".listJoins").html("");
    	$scope.nObj={title:"",description:"",creator:"1",join:"",important:"",deadline:"",joinName:"",responsibleName:"",fid:$scope.oid};
    };

	$scope.openSelectR=function(n){
		if(n==1){
			$scope.openSelectJ(0);
			$scope.isOpenResponsible=true;
			obj_service.getMember(apiUrl_service.url_getMember,$scope);
		}else
			$scope.isOpenResponsible=false;
	};
	$scope.selectResponsible=function(uid,name){
		$scope.nObj.responsibleName=name;
		$scope.nObj.responsible = uid;
		$scope.nTask.responsibleName=name;
		$scope.nTask.responsible = uid;
		$scope.isOpenResponsible=false;
	};
	$scope.openSelectJ=function(n){
		if(n==1){
			$scope.openSelectR(0);
			$scope.isOpenJoin=true;
			obj_service.getMember(apiUrl_service.url_getMember,$scope);
		}else
			$scope.isOpenJoin=false;
	};
	$scope.selectJoin=function(uid,name){
		if($scope.nObj.join==""){
			$scope.nObj.join=uid;
			var s="<i onclick='clearJoin("+uid+",this)'>"+name+"</i>";
			$(".listJoins").append(s);
			$scope.isOpenJoin=false;
		} else
		if($scope.nObj.join.indexOf(uid)==-1){
			$scope.nObj.join+=","+uid;
			var s="<i onclick='clearJoin("+uid+",this)'>"+name+"</i>";
			$(".listJoins").append(s);
		}
		$scope.isOpenJoin=false;
	};
	$scope.openEditObj=function(oid){
		//$scope.openSelectC(0);
		$scope.openSelectR(0);
		$scope.openSelectJ(0);
		$(".listJoins").html("");
		var u=apiUrl_service.url_objDetail +"/oid/"+oid;
		obj_service.getEdits(u,$scope,0);
		$scope.isEdit=true;
		//$scope.isNew=true;
		$scope.isTNew=false;
	};
	$scope.openEditTask=function(n,id){
		//$scope.openSelectC(0);
		$scope.openSelectR(0);
		$scope.openSelectJ(0);
		$(".listJoins").html("");
		if(n==0){
			$scope.isTNew=true;
			$scope.nTask={
				title:"",
				description:"",
				creator:"",
				responsible:"",
				important:"",
				start_time:"",
				deadline:"",
				oid:$scope.oid
			};
		}
		if(n==1){
			var u=apiUrl_service.url_taskDetail +"/tid/"+id;
			obj_service.getEdits(u,$scope,2);
			//$scope.isTNew=true;
			$scope.isEdit=true;
		}
		if(n==-1){
			$scope.isTNew=false;
			$scope.isEdit=false;
		}
	};
	$scope.saveObj=function(){
		var formData={
			title:$scope.nObj.title,
			description:$scope.nObj.description,
			creator:$scope.nObj.creator,
			join:$scope.nObj.join,
			responsible:$scope.nObj.responsible,
			important:$scope.nObj.important,
			deadline:$scope.nObj.deadline,
			fid:$scope.oid
		};
		if(!$scope.isEdit){
			obj_service.creatObj(apiUrl_service.url_createObjective,formData,$scope);
			var u = apiUrl_service.url_childObjective +"/oid/"+$rootScope.oid;
	    	obj_service.getMyObjs(u,$scope,1);
		}			
		else{
			formData.id=$scope.nObj.id;
			formData.fid=undefined;
			obj_service.creatObj(apiUrl_service.url_editObjective,formData,$scope);
	    	var u = apiUrl_service.url_childObjective +"/oid/"+$scope.oid;
	    	obj_service.getMyObjs(u,$scope,1);
	    	u = apiUrl_service.url_objDetail +"/oid/"+$scope.oid;
    		obj_service.getMyObjs(u,$scope,0);
		}  
	};
	$scope.saveTask=function(){
		var formData={
			title:$scope.nTask.title,
			description:$scope.nTask.description,
			creator:$scope.nTask.creator,
			responsible:$scope.nTask.responsible,
			important:$scope.nTask.important,
			start_time:$scope.nTask.start_time,
			deadline:$scope.nTask.deadline,
			oid:$scope.oid
		};
		if(!$scope.isEdit){
			//task_service.creatTask(apiUrl_service.url_addTask,formData,$scope);
			var promise = task_service.creatTask(apiUrl_service.url_addTask,formData,$scope);
			promise.then(function(response) {  // 调用承诺API获取数据 .resolve
				if($scope.subTlist == undefined){
					$scope.subTlist =[];
				}
				$scope.subTlist.unshift(response.data);
				$scope.daiban +=1;
			},function(response){
				alert(response.data);
			});

		}else{
			formData.id=$scope.nTask.id;
			var promise = task_service.creatTask(apiUrl_service.url_editTask,formData,$scope);
			promise.then(function(response) {  // 调用承诺API获取数据 .resolve
				if($scope.subTlist == undefined){
					$scope.subTlist =[];
				}
				if(response.data!=undefined)
					response.data.editShow = false;
				//$scope.daiban +=1;
			},function(response){
				alert(response.data);
			});
			$scope.closeTaskEdit(formData);
		}


	};

	$scope.closeTaskEdit = function(data){
		for(var i=0;i<$scope.subTlist.length;i++){
			if(data.id == $scope.subTlist[i].id){
				$scope.subTlist[i].editShow = false;
				$scope.subTlist[i].title = data.title;
				$scope.subTlist[i].description = data.description; //description:$scope.nTask.description,
				$scope.subTlist[i].responsible = data.responsible;//	responsible:$scope.nTask.responsible,
				$scope.subTlist[i].important = data.important;//	important:$scope.nTask.important,
				$scope.subTlist[i].start_time = data.start_time;//	start_time:$scope.nTask.start_time,
				$scope.subTlist[i].deadline = data.deadline;//:$scope.nTask.deadline,
				break;
			}
		}
	}

	$scope.delete = function(n,item){
		if(item.creator != $rootScope.loginUid ){
			alert('权限错误:不是创建人！');
			return false;
		}
		if(n!=1){
			if(($scope.mainOList[0].status == 9 || $scope.mainOList[0].status==-1)){
				alert('父目标已经被废弃或是撤销，操作失败');
				return false;
			}
		}

		if(n==0 || n==1){
			var u = apiUrl_service.url_changeObjectiveStatus+"/s/9/oid/"+item.id;
			obj_service.delete(u,n);
			window.location.reload();
		}
		else if(n==2){
			if(item.status == 1){
				$scope.yiban -=1;

			}
			if(item.status == 0){
				$scope.daiban -=1;
			}
			$scope.chexiao +=1;

			var u = apiUrl_service.url_changeTaskStatus+"/s/9/tid/"+item.id;
			obj_service.delete(u,n);
			item.status = 9;
		}
	};

	$scope.restore = function(n,item){
		if(item.creator != $rootScope.loginUid ){
			alert('权限错误:不是创建人！');
			return false;
		}
		if(n!=1){
			if(($scope.mainOList[0].status == 9 || $scope.mainOList[0].status==-1)){
				alert('父目标已经被废弃或是撤销，操作失败');
				return false;
			}
		}
		if(n==1 || n==0){
			var u = apiUrl_service.url_changeObjectiveStatus+"/s/0/oid/"+item.id;
			item.status = 0;
			obj_service.delete(u,n);
		}
		else if(n==2){

			$scope.chexiao -=1;
			$scope.daiban +=1;
			var u = apiUrl_service.url_changeTaskStatus+"/s/0/tid/"+item.id;
			obj_service.delete(u,n);
			item.status = 0;
		}
	};


	$scope.addTask = function($event,title){
		var keyCode = $event.keyCode;
		var date = new Date;
		var time = date.toLocaleDateString();
		if(keyCode == 13){
			if(title == undefined || title == null || title == ''){
				return false;
			}else{
				var formData = {
					title:title,
					create_time:time,
					description:'',
					creator:$rootScope.loginUid,
					responsible:$rootScope.loginUid,
					important:0,
					start_time:time,
					deadline:time,
					oid:$scope.oid
				};
				var promise = task_service.creatTask(apiUrl_service.url_addTask,formData,$scope);
				promise.then(function(response) {  // 调用承诺API获取数据 .resolve
					if($scope.subTlist == undefined){
						$scope.subTlist =[];
					}
						$scope.subTlist.unshift(response.data);
				},function(response){
					alert(response.data);
				});
				$scope.task.title = '';
				
			}
		}
	};

	$scope.setStatus = function(n){
		$scope.status = n;
		if(n==0)

			$scope.tabClass_task={doTask:"active-taskTab",findshTask:"",chexiaoTask:""};
		else if(n==1)
			$scope.tabClass_task={doTask:"",findshTask:"active-taskTab",chexiaoTask:""};
		else
			$scope.tabClass_task={doTask:"",findshTask:"",chexiaoTask:"active-taskTab"}

	};

	$scope.changeStatus = function(item){
		if(item.responsible != $rootScope.loginUid ){
			alert('权限错误:不是负责人！');
			return false;
		}
		if($scope.mainOList[0].status == 9 || $scope.mainOList[0].status==-1){
			alert('父目标已经被废弃或是撤销，操作失败');
			return false;
		}
		if(item.status == 1){
			$scope.yiban -=1;
			$scope.daiban +=1;
		}
		if(item.status == 0){
			$scope.daiban -=1;
			$scope.yiban +=1;
		}
		var u = apiUrl_service.url_changeTaskStatus+"/s/"+Math.abs(item.status-1)+"/tid/"+item.id;
		obj_service.delete(u,2);
		item.status = Math.abs(item.status-1);
		//u = apiUrl_service.url_objTask+"/oid/"+item.oid;
		//obj_service.getMyObjs(u,$scope,2);
	};
	$scope.dropEdit=function(id){
		if($scope.subTlist){
			var list=$scope.subTlist;
			for(var i=0;i<list.length;i++)
				if(id==list[i].id){
					if(!list[i].editShow)
						$scope.setCloseEdit();
					list[i].editShow=!list[i].editShow;
				}
			$scope.openEditTask(1,id);
		}
	};
	$scope.dropEditObj=function(id){
		if($scope.subOlist){
			$scope.subOlist=obj_service.objShowSwitch(id,$scope.subOlist);
			$scope.openEditObj(id);
		}
	};
	$scope.dropMyObj=function(id){
		if(!$scope.mainOList[0].editShow)
			$scope.setCloseEdit();
		$scope.mainOList[0].editShow=!$scope.mainOList[0].editShow;
		$scope.openEditObj(id);
	};
	$scope.setCloseEdit=function(){
		$scope.mainOList[0].editShow=false;
		var list=null;
		if($scope.subOlist){
			list=$scope.subOlist;
			for(var i=0;i<list.length;i++)
				list[i].editShow=false;
		}
		if($scope.subTlist){
			list=$scope.subTlist;
			for(i=0;i<list.length;i++)
				list[i].editShow=false;
		}
	};
	$scope.switchTree= function (id) {
		$scope.subOlist=obj_service.switchTree(id,$scope.subOlist);
	};
	$scope.enterObj=function(id,n){
		$scope.subOlist=obj_service.setTreeCur(id,$scope.subOlist);
		var u = apiUrl_service.url_objTask +"/oid/"+id;
		obj_service.getMyObjs(u,$scope,2);
		if(n!=undefined)
			$scope.obj_myClass="obj-cur-node";
		else
			$scope.obj_myClass="";
		$scope.oid=id;
		$scope.cancelNew();
	};
});