var shapedControllerV2 = {
	init:function(){
		this.bindEvent();
		var timer = setTimeout(function(){
			$('.shaped_v2').css('padding-top','2.6rem');
		},100)
	},
	bindEvent:function(){
		var _self = this;
		$('.list_item').off('click').on('click', function (e) {
            var productId = $(this).data('productid');
            var productName = $(this).find('dl dd h3').html().trim();
            var specialTitle = $(this).data('specialtitle');
            var title = $(this).data('title');
            try {
                _czc.push(['_trackEvent', 'v2异形banner'+specialTitle, title, '进入产品详情', '1', '']);
            } catch (e) {
                console.log('cnzz错误');
            }
            _self.goDetail(productId,productName);
        });
		$('.apply').on('click', function (e) {
            e.stopPropagation();
            var productName = $(this).parents('.list_item').find('dl dd h3').html().trim();
            var specialTitle = $(this).data('specialtitle');
            var title = $(this).data('title');
            try {
                _czc.push(['_trackEvent', 'v2异形banner'+specialTitle, title , '申请产品按钮', '1', '']);
            } catch (e) {
                console.log('cnzz错误');
            }
            _self.goApply({
                platformId: $(this).data('platformid'),
                productId: $(this).data('productid'),
                typeNid: $(this).data('typenid'),
                title: $(this).data('title'),
                mobile: $(this).data('mobile'),
                productName:productName
            });
       });
       $('body').on('touchstart',function(){
        	
        });
	},
	goDetail: function (productId,productName) {
        try {
            window.sd.sdProductDetail(productId);
        } catch (e) {
            console.log("Android跳转详情错误");
        }
        try {
            window.webkit.messageHandlers.sdProductDetail.postMessage({
                productId: productId
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
	shapedControllerV2.init();
})
