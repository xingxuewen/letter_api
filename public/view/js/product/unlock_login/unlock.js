var unlockLoginController = {
	init:function(){
		this.bindEvent();
		var timer = setTimeout(function(){
			$('.unlock_login').css('padding-top','2.52rem');
		},100)
	},
	bindEvent:function(){
		var _self = this;
		$('.list_item').off('click').on('click', function (e) {
            var productId = $(this).data('productid');
            var productName = $(this).find('dl dd h3').html().trim();
            var specialTitle = $(this).data('specialtitle');
            var id = $(this).data('id');
            var title = $(this).data('title');
            try {
                _czc.push(['_trackEvent', 'v2置顶专题'+specialTitle,title, '进入产品详情', '1', '']);
            } catch (e) {
                console.log('cnzz错误');
            }
            _self.goDetail(productId,productName,id);
        });
		$('.apply').on('click', function (e) {
			var productName = $(this).parents('.list_item').find('dl dd h3').html().trim();
			var specialTitle = $(this).data('specialtitle');
			var id = $(this).data('id');
			var title = $(this).data('title');
            e.stopPropagation();
            try {
                _czc.push(['_trackEvent', 'v2置顶专题'+specialTitle, title, '申请产品按钮', '1', '']);
            } catch (e) {
                console.log('cnzz错误');
            }
            _self.goApply({
                platformId: $(this).data('platformid'),
                productId: $(this).data('productid'),
                typeNid: $(this).data('typenid'),
                title: $(this).data('title'),
                mobile: $(this).data('mobile'),
                source: 'unlock_login_' + id,
                productName:productName			//h5用
            });
        });
        $('.unlock_next').on('click',function(e){
        	var unlockLoginId = $(this).data('unlockloginid');
        	var is_show_page = $(this).data('is_show_page');
        	try {
	            window.sd.sdUnlockNextProduct(JSON.stringify({
	            	'unlockLoginId':unlockLoginId,
	            	'is_show_page':is_show_page,
	            	"clickSource":'unlock_list'
	            }));
	        } catch (e) {
	            console.log("安卓联登解锁产品页面解锁下一批按钮错误");
	        }
	        try {
	            window.webkit.messageHandlers.sdUnlockNextProduct.postMessage({
	                'unlockLoginId':unlockLoginId,
	            	'is_show_page':is_show_page
	            });
	        } catch (e) {
	            console.log("ios联登解锁产品页面解锁下一批按钮错误");
	        }
        })
	},
	goDetail: function (productId,productName,id) {
        try {
            window.sd.sdProductDetail(productId);
        } catch (e) {
            console.log("Android跳转详情错误");
        }
        try {
            window.sd.sdProductDetailWithSource(JSON.stringify({
            	'id':productId,
            	'source':'unlock_login_' + id
            }));
        } catch (e) {
            console.log("Android跳转统计详情错误");
        }
        try {
            window.webkit.messageHandlers.sdProductDetail.postMessage({
                productId: productId,
                source:'unlock_login_' + id
            });
        } catch (e) {
            console.log("ios跳转详情错误");
        }
        try {
         	window.parent.postMessage({
               	'type': 'goDetail',
               	'productId':productId,
               	'productName':productName
           	}, '*');
           	return;
        } catch (e) {
            console.log("h5跳转详情错误");
        }
    },
    goApply: function (opt) {
        var androidData = JSON.stringify(opt);
        try {
            window.sd.sdProductWebView(androidData);
        } catch (e) {
            console.log("Android跳转产品H5错误");
        }
        try {
            window.webkit.messageHandlers.sdProductWebView.postMessage(opt);
        } catch (e) {
            console.log("ios跳转产品H5错误");
        }
        try {
            window.parent.postMessage({
               	'type': 'goApply',
               	'productData':opt
           	}, '*');
           	return;
        } catch (e) {
            console.log("h5跳转到产品h5错误");
        }
    }
}

$(function(){
	unlockLoginController.init();
})
