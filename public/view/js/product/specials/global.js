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
    },
    /*弹窗*/
    popupCover: function (opts) {
        $(".hintCover").remove();
        var defaults = {
            content: '',
            showTime: 2000,
            callback: ''
        };
        opts.callback;
        var option = $.extend({}, defaults, opts);
        var posTop = $(window).height() * .5;
        $('body').append('<div class="hintCover"><div class="hintPopup"></div></div>');
        $('.hintPopup').text(option.content);
        $('.hintCover').css({
            "position": "fixed",
            "top": 0,
            "left": 0,
            "width": 100 + "%",
            "height": 100 + "%",
            "background": "rgba(0,0,0,.2)",
            "z-index": "99999",
            "text-align": "center"
        });
        $('.hintPopup').css({
            "margin-top": posTop,
            "max-width": "6rem",
            "display": "inline-block",
            "height": ".76rem",
            "line-height": ".76rem",
            "text-align": "center",
            "background": "rgba(0, 0, 0, 0.8)",
            "color": '#fff',
            "font-size": .34 + "rem",
            "border-radius": .1 + "rem",
            "padding": "0 .35rem",
            "animation": "popupCover .12s ",
            "-webkit-animation": "popupCover .12s "
        });
        setTimeout(function () {
            $(".hintCover").fadeOut(300, option.callback);
            setTimeout(function () {
                $(".hintCover").remove();
            }, 500);
        }, .12 * 1000 + option.showTime);
    },
    /*加载动画*/
    addLoading: function (opts) {
        var defaults = {
            time: 6 * 1000
        };
        var option = $.extend({}, defaults, opts);
        $('.loadingCover').show();
        setTimeout(function () {
			$('.loadingCover').hide();
        }, option.time)
    },
    /*删除加载动画*/
    removeLoading: function (opts) {
        var defaults = {
            time: 400
        };
        var option = $.extend({}, defaults, opts);
        setTimeout(function () {
        	$('.loadingCover').hide();
        }, option.time)
    },
    /*触发登录*/
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
        try {
            window.parent.postMessage({
               	'type': 'goLogin'
           	}, '*');
       		return;
        } catch (e) {
            console.log("h5触发登录错误");
        }
    },
}
