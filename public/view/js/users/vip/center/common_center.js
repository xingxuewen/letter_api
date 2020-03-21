var commonCenterController = {
	init:function(){
		var mySwiper = new Swiper('.photo-swiper-container', {
			//可选选项，自动滑动
			autoplay: {
				delay: 3000,
				disableOnInteraction:false
			},
			slidesPerView: 5,
			offsetSlidesBefore:2,
      		spaceBetween: 0,
      		loop: true,
      		initialSlide:2,
      		noSwiping : true,
			noSwipingClass : 'stop-swiping',
      		centeredSlides : true,
      		on:{
      			slideChangeTransitionStart:function(){
      				var uid = $('.swiper-slide-active img').attr('uid');
      				commonCenterController.getVipDynamicData(uid);
      			}
      		}
		});
		commonCenterController.getStatusBarHeight();
	},
	//获取安卓顶部导航条距离
    getStatusBarHeight:function(){
    	var statusBarHeight = global.GetQueryString('statusBarHeight') === undefined?30:global.GetQueryString('statusBarHeight');
    	$('.common_center .top_box header').css('top',(Number(statusBarHeight)+10)/100 + 'rem');
    	var timer = setTimeout(function(){
    		var allHeight = $('.top_box').height() - $('.vip_privilege').offset().top + $('.list_mod').height();
    		$('.common_center').height(allHeight);
    	},800)
    },
	getVipDynamicData:function(uid){
		$.ajax({
			url: api_sudaizhijia_host + "/v1/users/vip/virtual",
			type: "get",
            dataType: "json",
            data: {
            	"uid" : uid
            },
            success: function (json) {
            	if (json.code == 200 && json.error_code == 0){
            		$('.common_center .content_info .name').html(json.data.username);
            		$('.common_center .content_info .date').html(json.data.minute);
            		if(json.data.message == ''){
            			$('.common_center .content_info .info').html('通过会员服务疯狂下款<span class="money" style="color: #d9b67b;">' + json.data.money + '</span>元');
            		}else{
            			$('.common_center .content_info .info').html(json.data.message);
            		}
            	}else{
            		global.popupCover({
	            		content:json.error_message
	            	})
            	}
            },
            error: function(){
            	global.popupCover({
            		content:json.error_message
            	})
            }
		});
	}
}
$(function(){
	commonCenterController.init();
	commonCenterController.getVipDynamicData();
})
