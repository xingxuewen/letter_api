var global = {
	init:function(){
		this.localStorage();
	},
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
     //判断浏览器是否支持无痕模式
    localStorage: function () {
        var localStorageSupported = ("localStorage" in window);
        if (!localStorageSupported) {
            global.popupCover({
                content : '请更新或使用现代浏览器'
            });
        } else {
            if (typeof localStorage === 'object') {
                try {
                    localStorage.setItem('localStorage', 1);
                    localStorage.removeItem('localStorage');
                } catch (e) {
                    global.popupCover({
                        content : '请关闭无痕模式!'
                    });
                };
            };
        }
    },
     //弹窗
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
    showCover: function ($showDom, $hideDom) {
        $(window).scrollTop(0);
        $($hideDom).hide();
        $($showDom).show();
        //添加历史记录
        history.pushState({
            prjCoverFloor: "prj-cover-floor"
        }, "");
        sessionStorage.prjCoverFloor = true;
        sessionStorage.showDom = $showDom;
        sessionStorage.hideDom = $hideDom;
    },
    hideCover: function () {
        $(window).scrollTop(0);
        $(sessionStorage.showDom).hide();
        $(sessionStorage.hideDom).show();
        sessionStorage.prjCoverFloor = false;
    }, // 监听历史记录来响应手机物理返回键
    popstate: function () {
        var _self = this;
        window.addEventListener("popstate", function (e) {
            if (String(sessionStorage.prjCoverFloor) == "true") {
                _self.hideCover();
            }
        }, false);
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
    /*会员中心返回按钮*/
    goBack: function () {
        $(document).scrollTop(0);
        try {
            window.sd.sdBack();
            return;
        } catch (e) {
            console.log("Android-Back方法返回错误");
        }
        try {
            window.sd.sdClose();
            return;
        } catch (e) {
            console.log("Android-close方法返回错误");
        }
        try {
            window.webkit.messageHandlers.sdClose.postMessage({});
            return;
        } catch (e) {
            console.log("ios-close方法返回错误");
        }
        try {
            window.webkit.messageHandlers.sdBack.postMessage({});
            return;
        } catch (e) {
            console.log("ios-back方法返回错误");
        }
        try {
            window.parent.postMessage({
            	'type' : 'returnBack'
            },'*');
            return;
        } catch (e) {
            console.log("h5方法返回错误");
        }
    },
 };
 
 global.init();
 
 
 window.onload = function () {
    document.documentElement.addEventListener('touchstart', function (event) {
        if (event.touches.length > 1) {
            event.preventDefault();
        }
    }, false);


    var lastTouchEnd = 0;
    document.documentElement.addEventListener('touchend', function (event) {
        var now = Date.now();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);
}
