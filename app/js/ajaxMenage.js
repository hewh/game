/*
 *	Ajax模块 
 */
(function(){
	var A={
		interfaceClass:".btnAjax",
		callAjax:function(){
			var t=$(this.interfaceClass).attr("callType"),_this=this;
			t=(t==undefined)?"click":t;
			$(this.interfaceClass).bind(t,function(){
				var a=$(_this.interfaceClass).attr("callAction");
				switch(a){
					case "receive":_this.receive(this);break;
				}
			});
		},
		receive:function(_this){/*接收到任务或目标*/
			/*0:表示接收成任务,1:表示接收成目标*/
			var t=(($(_this).attr("class")).indexOf("toTask")>=0)?0:1;
			var o={type:t},sUrl=$(_this).attr("callUrl"),fBack=function(data){
				alert(data);
			}
			this.runAjax(sUrl,o,fBack);
		},
		runAjax:function(url,o,fBack){
			$.post(url,o,fBack);
		}
	};
	A.callAjax();
})();
