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
		commonCenterController.bindEvent();
		commonCenterController.setAgreementTitle();
	},
	bindEvent:function(){
		$('.recharge_kind').on('click','.kind_item',function(){
			$(this).addClass('selected_kind').siblings().removeClass('selected_kind');
			var kindId = $('.recharge_kind .selected_kind .id').html();
			try {
	            window.sd.payMemberKind(JSON.stringify({
            		'subtype':kindId
	            }));
	            return;
	        } catch (e) {
	            console.log("Android统计用户点击购买会员类型错误");
	        }
	        try {
	            window.webkit.messageHandlers.payMemberKind.postMessage({
	            	'subtype':kindId
	            });
	            return;
	        } catch (e) {
	            console.log("ios统计用户点击购买会员类型错误");
	        }
		});
		//打开会员服务协议页面
		$('.open_vip_agreement').on('click',function(){
			try {
	            window.sd.sdAgreement(JSON.stringify({
	            	'title' : '会员服务协议',
	            	'url' : api_sudaizhijia_host + "/view/v2/users/identity/membership"
	            }));
	            return;
	        } catch (e) {
	            console.log("Android进入会员服务协议错误");
	        }
	        try {
	            window.webkit.messageHandlers.sdAgreement.postMessage({
	            	'title' : '会员服务协议',
	            	'url' : api_sudaizhijia_host + "/view/v2/users/identity/membership"
	            });
	            return;
	        } catch (e) {
	            console.log("ios进入会员服务协议错误");
	        }
	        try {
	            window.parent.postMessage({
	            	'type' : 'vipAgreement'
	            },'*');
	            return;
	        } catch (e) {
	            console.log("h5进入会员服务协议错误");
	        }
		});
		//点击立即开通/续费按钮
		$('.recharge_btn').on('click',function(){
			var kind = $('.recharge_kind .selected_kind .type_nid').html();
			if(kind == ''){
				console.log('type_nid为空');
				return;
			}
			try {
				//老版本接收String类型  不能为json字符串
	            window.sd.sdVipApply(kind);
	            return;
	        } catch (e) {
	            console.log("Android开通/续费会员错误");
	        }
	        try {
	            window.webkit.messageHandlers.sdVipApply.postMessage({
	            	'type':kind
	            });
	            return;
	        } catch (e) {
	            console.log("ios开通/续费会员错误");
	        }
	        try {
	            window.parent.postMessage({
	            	'type' : 'vipApply'
	            },'*');
	            return;
	        } catch (e) {
	            console.log("h5开通/续费会员错误");
	        }
		});
	},
	//协议名字设置
	setAgreementTitle:function(){
        var agreementName = global.GetQueryString('name') || '速贷之家';
        $('.open_vip_agreement span').text(agreementName);
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
