var basicController = {
    init: function () {
        var _self = this;
		this.bindEvent();
        this.checkTransmitMoney();
        this.inputBack();
        this.advertisement.init();
        this.checkHideOption();
        this.checkMoney();
        this.submitBtn();
        this.getAgreement();
        global.scrollHeader();
    },
    //监听h5message传值变化
    bindEvent:function(){
    	window.addEventListener('message', function(e) {
        	switch (e.data.type){
        		//获取token方法
        		case 'getToken':
		    		if($('.token').length == 0){
			        	$('body').append("<div class='token' style='display:none'>"+e.data.token+"</div>");
			       	}
        			break;
        	}
        }, false);
    },
    //h5站插入token
    setToken:function(){
    	var _self = this;
    	setTimeout(function(){
    		if($('.token').length == 0){
	        	var h5Token = _self.GetQueryString(location.href,'token') || '';
	        	$('body').append("<div class='token' style='display:none'>"+h5Token+"</div>");
	        }
    	},800)
    },
    //判断传入金额显示隐藏信息
    checkTransmitMoney: function () {
        var transmitMoney = global.GetQueryString('money') || '';
        if (transmitMoney !== '') {
            $('#basic-money').val(transmitMoney);
            if (transmitMoney < 5000) {
                $('#basic-data').addClass('show');
            } else {
                $('#basic-data').removeClass('show');
            }
        } else {
            $('#basic-money').val('5000');
        }
    },
    inputBack: function () {
        $('.money_label').click(function () {
            var inputMoney = $('#basic-money').val();
            $('#basic-money').val('');
            $('#basic-money').val(inputMoney);
        })
    },
    /*单行广告轮播*/
    advertisement: {
        init: function () {
            this.random(0);
            this.poster();
            this.applyNum();
        },
        poster: function () {
            var _self = this;
            var liHeight = $("#inducement-ul li").height(); //一个li的高度
            setInterval(function () {
                _self.random(1)
                $("#inducement-ul").animate({
                    top: -liHeight
                }, 500, function () {
                    $("#inducement-ul li").eq(0).appendTo($("#inducement-ul"));
                    $("#inducement-ul").css({
                        "top": 0
                    });
                })
            }, 3000)
        },
        random: function (idx) {
            //申请数
            var applyNum = function () {
                var oDate = new Date();
                var timeS = oDate.getHours() * 60 * 60 + oDate.getMinutes() * 60 + oDate.getSeconds();
                var applyNum = timeS * 3 + 2000;
                $("#inducement-ul li").eq(idx).find('.applyNum').text(applyNum);
            }
            applyNum();
            //2.电话号码
            var phone = function () {
                var prefixArray = new Array("130", "131", "132", "133", "135", "137", "138", "170", "187", "189");
                var i = parseInt(10 * Math.random());
                var prefix = prefixArray[i];
                for (var j = 0; j < 8; j++) {
                    prefix = prefix + Math.floor(Math.random() * 10);
                }
                var phone = prefix.substring(0, 3) + "****" + prefix.substring(7, 11);
                $("#inducement-ul li").eq(idx).find(".random_mobile").text(phone);
            }
            phone();
            //3.价钱
            var rnd = function () {
                var min = 10;
                var max = 100;
                var money = min + Math.floor(Math.random() * (max - min + 1));
                $("#inducement-ul li").eq(idx).find(".random_money").text(money + "000");
            }
            rnd()
            //平台
            var products = function () {
                var arr = ["你我贷", "融时代", "小小金融", "助贷网", "厚本金融", "非秒贷", "秒贷", "恒昌", "中腾信", "东方金融", "新一贷", "氧气贷", "拍拍贷", "秒啦"]
                var city = arr[Math.floor(Math.random() * arr.length)];
                $("#inducement-ul li").eq(idx).find(".platform").text(city);
            }
            products();
        },
        applyNum: function () {
            //申请数
            var oDate = new Date();
            var timeS = oDate.getHours() * 60 * 60 + oDate.getMinutes() * 60 + oDate.getSeconds();
            var applyNum = timeS * 3 + 2000;
            $("#inducement-ul li").eq(0).find('.applyNum').text(applyNum);
            setInterval(function () {
                applyNum += 27;
                $("#inducement-ul li").eq(1).find('.applyNum').text(applyNum);
            }, 9000);
        }
    }, //金额判断
    checkMoney: function () {
        var _self = this;
        $('#basic-money').on('blur', function () {
            var val = $.trim($(this).val()),
                resultVal = val;
            if (val !== '' && val < 100) {
                global.popupCover({
                    content: "最小额度为100"
                });
                resultVal = '100';
                $('#basic-data').slideDown(200, function () {
                    $('#basic-data').addClass('show');
                });
            } else if (val >= 100 && val < 5000) {
                $('#basic-data').slideDown(200, function () {
                    $('#basic-data').addClass('show');
                });
            } else if (val > 1000000) {
                global.popupCover({
                    content: "最大额度为1000000",
                });
                resultVal = '1000000';
            }
            $('#basic-money').val(resultVal);
            _self.ransmitMoney(resultVal);
        }).on('input propertychange', function () {
            var val = $.trim($(this).val());
            if (val >= 5000) {
                $('#basic-data').slideUp(200, function () {
                    $('#basic-data').removeClass('show')
                });
            }
        })
    }, //传递金额
    ransmitMoney: function (money) {
        try {
            window.sd.sdSetAmount(money);
        } catch (e) {
            console.log("Android传递金额错误");
        }
        try {
            window.webkit.messageHandlers.sdSetAmount.postMessage({
                money: money
            });
        } catch (e) {
            console.log("ios传递金额错误");
        }
    }, //隐藏的选项判断
    checkHideOption: function () {
        global.checkName($('#basic-name'));
        global.checkIdCard($('#basic-idcard'));
        //城市
        setTimeout(function () {
            var location = $('.location').text();
            if (location !== '' && $('#basic-city').val() == '') {
                $('#basic-city').val(location);
            }
        }, 1000);
        $('#basic-city').on('click', function () {
            history.pushState({}, null, './basic?page=citys_basic');
            $('.header_title').text('选择城市');
            global.getCity();
            $(this).blur();
        });
    }, //协议
    getAgreement: function () {
        $('#agreement_icon').on('click', function () {
            $(this).toggleClass('onSelected');
        });
        $('#agreement_btn').on('click', function () {
            history.pushState({}, null, './basic?page=agreement');
            if ($('#agreement-page').html() === '') {
                $.get(api_sudaizhijia_host + "/view/oneloan/agreement", function (result) {
                    $('#agreement-page').html(result);
                });
            }
            $('#agreement-page').show().siblings('div').hide();
            $('.header_title').text('用户协议');
        })
    }, //提交按钮
    submitBtn: function () {
        var _self = this;
        $('#basic-submit').on('click', function () {
            var moneyVal = $.trim($('#basic-money').val());
            if (moneyVal == '') {
                global.popupCover({
                    content: "请输入金额"
                });
            } else {
                if (moneyVal < 5000) {
                    var showMoreOption = $('#basic-data').hasClass('show');
                    if (showMoreOption) {
                        _self.checkSubmit();
                    } else {
                        $('#basic-data').slideDown(200, function () {
                            $('#basic-data').addClass('show');
                        });
                    }
                } else {
                    var agreement_icon = $('#agreement_icon').hasClass('onSelected') ? true : false;
                    if (!agreement_icon) {
                        global.popupCover({
                            content: "请勾选协议"
                        });
                        return false;
                    }
                    var token = $.trim($('.token').text()) ||  '';
                    
                    if (token == '') {
                        global.sdLogin();
                    } else {
                        $(document).scrollTop(0);
                        if ($('#full-page').html() === '') {
                            global.addLoading();
                            $.get(api_sudaizhijia_host + "/view/oneloan/full", function (result) {
                                $('#full-page').html(result);
                                $('#full-page').show().siblings('div').hide();
                                global.removeLoading({
                                    time: 800
                                });
                            });
                        } else {
                            $('#full-page').show().siblings('div').hide();
                        };
                        history.pushState({}, null, './basic?page=full');
                    }
                }
            }
        })
    },
    //获取地址参数方法
    GetQueryString: function (url,name) {
        var object = {};
        if (url.indexOf("?") != -1) {
        	var index = url.indexOf("?");
            var str = url.substr(index+1);
            var strs = str.split("&");
            for (var i = 0; i < strs.length; i++) {
                object[strs[i].split("=")[0]] = strs[i].split("=")[1]
            }
        }
        return object[name];
    },
    //判断信息填写
    checkSubmit: function () {
        var _self = this;
        var validator = new IDValidator();
        var IDcard = validator.isValid($.trim($('#basic-idcard').val()));
        var agreement_icon = $('#agreement_icon').hasClass('onSelected') ? true : false;
        if (!nameZ.test($('#basic-name').val())) {
            global.popupCover({
                content: "请输入正确姓名"
            });
            $('#basic-name').focus().parent('div').addClass('errorStyle');
            return false;
        } else if (!IDcard) {
            global.popupCover({
                content: "请输入正确身份证号"
            });
            $('#basic-idcard').focus().parent('div').addClass('errorStyle');
            return false;
        } else if ($('#basic-city').val() === '') {
            global.popupCover({
                content: "请选择城市信息"
            });
            $('#basic-city').focus().parent('div').addClass('errorStyle');
            return false;
        } else if (!agreement_icon) {
            global.popupCover({
                content: "请勾选协议"
            });
            return false;
        } else {
            _self.postData();
        }
    },
    postData: function () {
        var _self = this;
        var token = $('.token').text() || '';
        var certificate_no = $('#basic-idcard').val();
        if (token == '') {
            global.sdLogin();
        } else {
            global.addLoading();
            global.getCardInfo(certificate_no);
            $.ajax({
                url: api_sudaizhijia_host + "/oneloan/v1/spread/basic",
                type: "post",
                dataType: "json",
                data: {
                    money: $('#basic-money').val(),
                    name: $('#basic-name').val(),
                    certificate_no: certificate_no,
                    sex: global.sex,
                    birthday: global.age,
                    city: $('#basic-city').val()
                },
                success: function (result) {
                    if (result.code == 200 && result.error_code == 0) {
                        $(document).scrollTop(0);
                        var deviceId = $('.deviceId').text();
                        var terminalType = $('.terminalType').text();
                        $.get(api_sudaizhijia_host + "/view/oneloan/products", {
                            deviceId: deviceId,
                            terminalType: terminalType
                        }, function (result) {
                            history.pushState({}, null, './basic?page=result_basic');
                            $('.header_title').text('我的专属贷款推荐');
                            $('#result-page').html(result);
                            $('#result-page').show().siblings('div').hide();
                            global.removeLoading();
                        });
                    } else {
                        global.popupCover({
                            content: result.error_message
                        });
                        global.removeLoading();
                    }
                }
            })
        }
    }
};
$(function () {
    basicController.init();
})
