var specialTopController = {
	init:function(){
		this.bindEvent();
		var timer = setTimeout(function(){
			$('.special_top').css('padding-top','2.6rem');
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
                source: 'zhuanti_' + id,
                productName:productName			//h5用
            });
        });
        $('.special_recom .special_img img').on('click', function (e) {
            e.stopPropagation();
            _self.goSpecial({
                id: $(this).data('id'),
                title: $(this).data('title'),
                special_sign: $(this).data('sign')
            });
        });
        $('body').on('touchstart',function(){
        	
        });
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
            	'source':'zhuanti_' + id
            }));
        } catch (e) {
            console.log("Android跳转统计详情错误");
        }
        try {
            window.webkit.messageHandlers.sdProductDetail.postMessage({
                productId: productId,
                source:'zhuanti_' + id
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
    },
    goSpecial: function(opt){
    	var androidData = JSON.stringify(opt);
        try {
            window.sd.specialReload(androidData);
        } catch (e) {
            console.log("Android跳转分类专题错误");
        }
        try {
            window.webkit.messageHandlers.specialReload.postMessage(opt);
        } catch (e) {
            console.log("ios跳转分类专题错误");
        }
        try {
            window.parent.postMessage({
               	'type': 'goSpecial',
               	'specialData':opt
           	}, '*');
           	return;
        } catch (e) {
            console.log("h5跳转到分类专题错误");
        }
    }
}

$(function(){
	specialTopController.init();
})
