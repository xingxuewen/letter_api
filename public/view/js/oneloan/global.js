var nameZ = /^[\u4E00-\u9FA5\uf900-\ufa2d]{1,10}·?[\u4E00-\u9FA5\uf900-\ufa2d]{1,10}$/;
//var nameZ = /^[\u4e00-\u9fa5]+[·•][\u4e00-\u9fa5]+$/;
//var nameZ = /^[\u4e00-\u9fa5]{2,21}$/;
var global = {
    //截取地址栏信息
    GetQueryString: function (name) {
        var url = decodeURI(location.search);
        var object = {};
        if (url.indexOf("?") != -1) {
            var str = url.substr(1);
            var strs = str.split("&");
            for (var i = 0; i < strs.length; i++) {
                object[strs[i].split("=")[0]] = strs[i].split("=")[1]
            }
        }
        return object[name];
    }
    , /*姓名*/
    checkName: function ($dom, callback) {
        var _self = this;
        $dom.on('blur', function () {
            var nameVal = $.trim($dom.val());
            if (nameVal !== '' && !nameZ.test(nameVal)) {
                $dom.parent('div').addClass('errorStyle');
                $('#base_info .basic_name').addClass('error_pro');
                _self.popupCover({
                    content: '姓名格式错误'
                })
            }
            if (callback) {
                callback();
            }
        }).bind('input propertychange', function () {
            $dom.parent('div').removeClass('errorStyle');
            $('#base_info .basic_name').removeClass('error_pro');
        });
    }
    , getCardInfo: function (card) {
        var _self = this;
        var UUserCard = $.trim(card);
        var valLen = UUserCard.length;
        if (valLen === 15) {
            if (parseInt(UUserCard.substr(-1, 1)) % 2 == 1) {
                //男
                _self.sex = '1';
            }
            else {
                //女
                _self.sex = '0';
            }
            var age = '19' + UUserCard.substr(6, 6);
            _self.age = age;
        }
        else if (valLen === 18) {
            if (parseInt(UUserCard.substr(16, 1)) % 2 == 1) {
                //男
                _self.sex = '1';
            }
            else {
                //女
                _self.sex = '0';
            }
            var age = UUserCard.substr(6, 8);
            _self.age = age;
        }
        else {
            _self.age = '';
            _self.sex = '';
        }
    }
    , /*身份证*/
    checkIdCard: function ($dom, callback) {
        var _self = this;
        var validator = new IDValidator();
        //判断身份证号码性别男女
        $dom.bind("input propertychange", function () {
            $dom.parent('div').removeClass('errorStyle');
            $('#base_info .basic_card').removeClass('error_pro');
        }).blur(function (e) {
            var idcardVal = $dom.val();
            var res = validator.isValid(idcardVal);
            if (idcardVal !== '') {
                if (!res) {
                    $dom.parent('div').addClass('errorStyle');
                    $('#base_info .basic_card').addClass('error_pro');
                    _self.popupCover({
                        content: '身份证号格式错误'
                    });
                }
            }
            if (callback) {
                callback();
            }
        });
    }
    , /*弹窗*/
    popupCover: function (opts) {
        $(".hintCover").remove();
        var defaults = {
            content: ''
            , showTime: 2000
            , callback: ''
        };
        opts.callback;
        var option = $.extend({}, defaults, opts);
        var posTop = $(window).height() * .5;
        $('body').append('<div class="hintCover"><div class="hintPopup"></div></div>');
        $('.hintPopup').text(option.content);
        $('.hintCover').css({
            "position": "fixed"
            , "top": 0
            , "left": 0
            , "width": 100 + "%"
            , "height": 100 + "%"
            , "background": "rgba(0,0,0,.2)"
            , "z-index": "99999"
            , "text-align": "center"
        });
        $('.hintPopup').css({
            "margin-top": posTop
            , "max-width": "6rem"
            , "display": "inline-block"
            , "height": ".76rem"
            , "line-height": ".76rem"
            , "text-align": "center"
            , "background": "rgba(0, 0, 0, 0.8)"
            , "color": '#fff'
            , "font-size": .34 + "rem"
            , "border-radius": .1 + "rem"
            , "padding": "0 .35rem"
            , "animation": "popupCover .12s "
            , "-webkit-animation": "popupCover .12s "
        });
        setTimeout(function () {
            $(".hintCover").fadeOut(300, option.callback);
            setTimeout(function () {
                $(".hintCover").remove();
            }, 500);
        }, .12 * 1000 + option.showTime);
    }
    , /*加载动画*/
    addLoading: function (opts) {
        var defaults = {
            time: 6 * 1000
        };
        var option = $.extend({}, defaults, opts);
        $('.loadingCover').show();
        setTimeout(function () {
            $('.loadingCover').hide();
        }, option.time)
    }
    , /*删除加载动画*/
    removeLoading: function (opts) {
        var defaults = {
            time: 400
        };
        var option = $.extend({}, defaults, opts);
        setTimeout(function () {
            $('.loadingCover').hide();
        }, option.time)
    }
    , /*返回按钮*/
    goBack: function () {
        $(document).scrollTop(0);
        $('.header_title').text('一键选贷款');
        $('#apply-iframe').attr('src', '');
        //        $('#result-page').html('');
        var backPage = this.GetQueryString('page') || 'basic';
        if (backPage == 'citys_basic' || backPage == 'full' || backPage == 'agreement' || backPage == 'result_basic') {
            $('#container>div').hide();
            $('#basic-page').show();
            history.pushState({}, null, './basic?page=basic');
        }
        else if (backPage == 'result_full' || backPage == 'citys_full') {
            $('#container>div').hide();
            $('#full-page').show();
            history.pushState({}, null, './basic?page=full');
        }
        else if (backPage == 'apply_view_basic') {
            $('#container>div').hide();
            $('#result-page').show();
            history.pushState({}, null, './basic?page=result_basic');
        }
        else if (backPage == 'apply_view_full') {
            $('#container>div').hide();
            $('#result-page').show();
            history.pushState({}, null, './basic?page=result_full');
        }
        else if (backPage == 'basic') {
            try {
                window.sd.sdClose();
            }
            catch (e) {
                console.log("Android返回错误");
            }
            try {
                window.webkit.messageHandlers.sdClose.postMessage({});
            }
            catch (e) {
                console.log("ios返回错误");
            }
            try {
	            window.parent.postMessage({
	               	'type': 'returnBack'
	           	}, '*');
	           	return;
	        } catch (e) {
	            console.log("h5返回错误");
	        }
        }
    }
    , /*触发登录*/
    sdLogin: function () {
        try {
            window.sd.sdLogin();
        }
        catch (e) {
            console.log("Android触发登录错误");
        }
        try {
            window.webkit.messageHandlers.sdLogin.postMessage({});
        }
        catch (e) {
            console.log("ios触发登录错误");
        }
        try {
            window.parent.postMessage({
               	'type': 'goLogin'
           	}, '*');
       		return;
        } catch (e) {
            console.log("h5触发登录错误");
        }
    }, //头部滑动样式
    scrollHeader: function () {
        $(document).scroll(function () {
            if ($(this).scrollTop() > 60) {
                $('.header').addClass('changeStyle');
            }
            else {
                $('.header').removeClass('changeStyle');
            }
        });
    }
    , /*城市选择*/
    getCity: function (cityCallback) {
        var _self = this;
        if ($('#city_cover_box').html() === '') {
            _self.addLoading();
            $.get(api_sudaizhijia_host + "/view/oneloan/citys", function (result) {
                $('#city_cover_box').html(result);
                $('#city_cover_box').show().siblings('div').hide();
                _self.removeLoading();
            });
        }
        else {
            $('#city_cover_box').show().siblings('div').hide();
        }
        if (cityCallback) {
            this.cityCallback = true;
        }
    }
}
