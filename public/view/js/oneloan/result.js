var resultController = {
    init: function () {
        this.eventView();
    },
    eventView: function () {
        var _self = this;
        $('.back_btn').off('click').on('click', function () {
            $('.header_title').text('一键选贷款');
        });
        $(document).off('click').on('click', '.product_list', function (e) {
            var productId = $(this).data('productid');
            var productName = $(this).find('dl dd h3').html().trim();
            _self.goDetail(productId,productName);
        });
        $(document).on('click', '.apply', function (e) {
            e.stopPropagation();
            _self.goApply({
                platformId: $(this).data('platformid'),
                productId: $(this).data('productid'),
                typeNid: $(this).data('typenid'),
                title: $(this).data('title'),
                mobile: $(this).data('mobile')
            });
        });
        $('#more-products-btn').off('click').on('click', function () {
            _self.goProductList();
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
               	'data':opt
           	}, '*');
           	return;
        } catch (e) {
            console.log("h5跳转到产品h5错误");
        }
    },
    goProductList: function () {
        try {
            window.sd.sdProductList();
        } catch (e) {
            console.log("Android跳转到产品大全错误");
        }
        try {
            window.webkit.messageHandlers.sdProductList.postMessage({});
        } catch (e) {
            console.log("ios跳转到产品大全错误");
        }
        try {
            window.parent.postMessage({
               	'type': 'goProduct'
           	}, '*');
           	return;
        } catch (e) {
            console.log("h5跳转到产品大全错误");
        }
    },

};
$(function () {
    resultController.init();
})
