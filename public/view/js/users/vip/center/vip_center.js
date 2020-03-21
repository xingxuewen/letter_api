var vipCenterController = {
	init:function(){
		this.advertisement.init();
		this.bindEvent();
		this.getStatusBarHeight();
	},
	/*单行广告轮播*/
    advertisement: {
        init: function () {
            this.poster();
        },
        poster: function () {
            var _self = this;
            var liHeight = $("#inducement-ul li").height(); //一个li的高度
            var liLength = $("#inducement-ul li").length;	//li的数量
            var i = 1;
            setInterval(function () {
                $("#inducement-ul").animate({
                    top: -i * liHeight
                }, 500, function () {
					i++;
                    if(i > liLength){
                    	$("#inducement-ul").css({
	                        "top": 0
	                    });
	                    i = 1;
                    }
                })
            }, 3000)
        }
    },
    //跳转续费
    sdLogin: function () {
        try {
            window.sd.sdLogin();
        } catch (e) {
            console.log("Android触发登录错误");
        }
        try {
            window.webkit.messageHandlers.sdLogin.postMessage({});
        } catch (e) {
            console.log("ios触发登录错误");
        }
    },
    //获取安卓顶部导航条距离
    getStatusBarHeight:function(){
    	var statusBarHeight = global.GetQueryString('statusBarHeight') === undefined?30:global.GetQueryString('statusBarHeight');
    	$('.vip_center .top_box header').css('top',(Number(statusBarHeight) + 10)/100 + 'rem');
    	$('.vip_center .top_box .user_info').css('margin-top',(Number(statusBarHeight) + 10)/100  + 'rem');
    },
    bindEvent:function(){
    	//立即续费
    	$('.vip_center .renew_btn').on('click',function(){
    		try {
	            window.sd.sdVipApply();
	        } catch (e) {
	            console.log("Android续费错误");
	        }
	        try {
	            window.webkit.messageHandlers.sdVipApply.postMessage({});
	        } catch (e) {
	            console.log("ios续费错误");
	        }
    	});
    	//点击领取按钮  调起二维码页面
    	$('.vip_center .privilege_item img,.vip_center .privilege_item .menu').on('click',function(){
			if($(this).parent().find('.menu').length != 0){
				try {
		            window.sd.sdVipOneByOneDialog();
		        } catch (e) {
		            console.log("Android弹出二维码页面错误");
		        }
		        try {
		            window.webkit.messageHandlers.sdVipOneByOneDialog.postMessage({});
		        } catch (e) {
		            console.log("ios弹出二维码页面错误");
		        }
			}else{
				return;
			}
    	});
    	//点击福利积分 跳转积分商城
    	$('.vip_center .integral_item img').on('click',function(){
    		var app_url = $(this).attr('url');
    		var name = $(this).attr('name');
    		if((app_url.indexOf('http') != -1 || app_url.indexOf('https') != -1) && app_url.indexOf('dbredirect') != -1){
    			$.ajax({
    				url: app_url,
					type: "get",
		            dataType: "json",
		            success: function (json) {
		            	if(json.code == 200 && json.error_code == 0){
		            		vipCenterController.goIntegralMall(json.data.redirect_url,name);
		            	}
		            },
		            error:function(){
		            	global.popupCover({
		            		content:json.error_message
		            	})
		            }
    			});
    		}else{
    			vipCenterController.goIntegralMall(app_url);
    		}
    	});
    },
    //安卓ios方法跳转积分商城
	goIntegralMall:function(app_url,name){
		try {
            window.sd.sdIntegralMall(JSON.stringify({
            	'app_url' : app_url,
            	'name' : name
            }));
        } catch (e) {
            console.log("Android进入积分商城错误");
        }
        try {
            window.webkit.messageHandlers.sdIntegralMall.postMessage({
            	'app_url' : app_url,
            	'name' : name
            });
        } catch (e) {
            console.log("ios进入积分商城错误");
        }
	}
}


$(function(){
	vipCenterController.init();
})

