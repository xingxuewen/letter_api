var resultController = {
    init: function () {
        this.eventView();
        this.productJump();
    },
    eventView: function () {
        var _self = this;
        $('.back_btn').off('click').on('click', function () {
            $('.header_title').text('一键选贷款');
        });
        $('#more-products-btn').off('click').on('click', function () {
            try {
                _czc.push(['_trackEvent', '一键贷功能', '更多产品点击', '', '1', '']);
            } catch (e) {
                console.log('cnzz错误');
            }
            _self.goProductList();
        });
    },
    productJump() {
        $(document).on('click', '.product_list', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            global.addLoading();
            apply();

            function apply() {
                $.ajax({
                    url: api_sudaizhijia_host + '/v1/oauth/oneloan/application',
                    type: 'get',
                    data: {
                        id: id
                    },
                    success: function (res) {
                        if (res.code == 200 && res.error_code == 0) {
                            try {
                                _czc.push(['_trackEvent', '一键贷功能', name, '立即申请', '1', '']);
                            } catch (e) {
                                console.log('cnzz错误');
                            }
                            var backPageName = global.GetQueryString('page');
                            if (backPageName == 'result_basic') {
                                history.pushState({}, null, './basic?page=apply_view_basic');
                            } else if (backPageName == 'result_full') {
                                history.pushState({}, null, './basic?page=apply_view_full');
                            }
                            $('#apply_view').show().siblings('div').hide();
                            $('.header_title,#apply-name').text(name);
                            $('#apply-iframe').attr('src', res.data.url);
                            $('#apply-iframe').css({
                                height: $(window).height() - $('#apply-name').height()
                            });
                            global.removeLoading({
                                time: 1000
                            });
                        } else {
                            global.popupCover({
                                content: res.error_message,
                            })
                        }
                    }
                })
            }
        })
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
