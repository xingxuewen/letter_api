$(function () {
    dataController.initView();
    dataController.bindEvent();
})
var dataController = {
    addPercent: {
        base: false
        , job: false
        , asset: false
        , credit: false
    }
    , initView: function () {
        $(".knob").knob({}); //初始化进度条插件
        //      dataController.fixedLoansBtn();
        dataController.checkComplete();
        global.checkName($("#base_name"), dataController.checkBasicInfo);
        global.checkIdCard($("#base_card"), dataController.checkBasicInfo);
        if ($('.location').length != 0 && $('.location').text() != '' && $('#base_city').val() == '') {
            $('#base_city').val($('.location').text());
        }
    }, //检查基本信息填写是否完整
    checkBasicInfo: function () {
        var nameZ = /^[\u4E00-\u9FA5\uf900-\ufa2d]{1,10}·?[\u4E00-\u9FA5\uf900-\ufa2d]{1,10}$/;
        var nameVal = $.trim($('#base_name').val());
        var nameCorrect = (nameVal != '' && nameZ.test(nameVal));
        var idcardVal = $('#base_card').val();
        var validator = new IDValidator();
        var res = validator.isValid(idcardVal);
        var idcardCorrect = (idcardVal != '' && res);
        var cityVal = $('#base_city').val();
        if (nameCorrect && idcardCorrect && cityVal != '' && !dataController.addPercent.base) {
            $('#base_info .success_percent').html('<span style="color:#fe5c0d;">已完成</span>');
            $('#base_info .success_percent').attr('isComplete', '1');
            var curPercent = parseInt($('.knob').val());
            $('.knob').val(curPercent + 20).trigger("change");
            $('#percentNum').html(curPercent + 20);
            dataController.addPercent.base = true;
        }
        else {
            if (dataController.addPercent.base && (!nameCorrect || !idcardCorrect || cityVal == '')) {
                var curPercent = parseInt($('.knob').val());
                $('#base_info .success_percent').html('完善工作信息，下款成功率+20%');
                $('#base_info .success_percent').attr('isComplete', '0');
                $('.knob').val(curPercent - 20).trigger("change");
                $('#percentNum').html(curPercent - 20);
                dataController.addPercent.base = false;
            }
        }
    }, //检查各版块是否含有历史信息
    checkComplete: function () {
        dataController.addPercent = {
            base: $('#base_info .success_percent').attr('isComplete') == '1' ? true : false
            , job: $('#job_info .success_percent').attr('isComplete') == '1' ? true : false
            , asset: $('#asset_info .success_percent').attr('isComplete') == '1' ? true : false
            , credit: $('#credit_info .success_percent').attr('isComplete') == '1' ? true : false
        }
    }
    , bindEvent: function () {
        var _this = this;
        //收缩板块信息
        $('#base_info .stretch_btn').click(function () {
            if ($('#base_info .inp_area').css('display') == 'none') {
                $(this).css('background', 'url(/view/img/oneloan/full_bottom_icon.png) no-repeat center center');
                $(this).css('background-size', '.28rem .18rem');
                $('#base_info .info_title').css('border-bottom', '1px solid #e1e1e1');
            }
            else {
                $(this).css('background', 'url(/view/img/oneloan/full_right_icon.png) no-repeat center center');
                $(this).css('background-size', '.18rem .28rem');
                $('#base_info .info_title').css('border-bottom', 'none');
            }
            $('#base_info .inp_area').stop(false, false).slideToggle();
        })
        $('#job_info .stretch_btn').click(function () {
            if ($('#job_info .job_sel_box').css('display') == 'none') {
                $(this).css('background', 'url(/view/img/oneloan/full_bottom_icon.png) no-repeat center center');
                $(this).css('background-size', '.28rem .18rem');
                $('#job_info .info_title').css('border-bottom', '1px solid #e1e1e1');
            }
            else {
                $(this).css('background', 'url(/view/img/oneloan/full_right_icon.png) no-repeat center center');
                $(this).css('background-size', '.18rem .28rem');
                $('#job_info .info_title').css('border-bottom', 'none');
            }
            $('#job_info .job_sel_box').stop(false, false).slideToggle();
        })
        $('#asset_info .stretch_btn').click(function () {
            if ($('#asset_info .asset_sel_box').css('display') == 'none') {
                $(this).css('background', 'url(/view/img/oneloan/full_bottom_icon.png) no-repeat center center');
                $(this).css('background-size', '.28rem .18rem');
                $('#asset_info .info_title').css('border-bottom', '1px solid #e1e1e1');
            }
            else {
                $(this).css('background', 'url(/view/img/oneloan/full_right_icon.png) no-repeat center center');
                $(this).css('background-size', '.18rem .28rem');
                $('#asset_info .info_title').css('border-bottom', 'none');
            }
            $('#asset_info .asset_sel_box').stop(false, false).slideToggle();
        })
        $('#credit_info .stretch_btn').click(function () {
                if ($('#credit_info .credit_sel_box').css('display') == 'none') {
                    $(this).css('background', 'url(/view/img/oneloan/full_bottom_icon.png) no-repeat center center');
                    $(this).css('background-size', '.28rem .18rem');
                    $('#credit_info .info_title').css('border-bottom', '1px solid #e1e1e1');
                }
                else {
                    $(this).css('background', 'url(/view/img/oneloan/full_right_icon.png) no-repeat center center');
                    $(this).css('background-size', '.18rem .28rem');
                    $('#credit_info .info_title').css('border-bottom', 'none');
                }
                $('#credit_info .credit_sel_box').stop(false, false).slideToggle();
            })
            //点击选择城市信息
        $('#base_city').click(function () {
                history.pushState({}, null, './basic?page=citys_full');
                $('.header_title').text('选择城市');
                global.getCity('1');
            })
            //点击返回按钮
        $('#full_top .go_back').click(function () {
                global.goBack();
            })
            //点击立即贷款按钮
        $('#full_loans .loans_btn').click(function () {
                var basicComplete = $('#base_info .success_percent').attr('isComplete')
                    , jobComplete = $('#job_info .success_percent').attr('isComplete')
                    , assetComplete = $('#asset_info .success_percent').attr('isComplete')
                    , creditComplete = $('#credit_info .success_percent').attr('isComplete');
                if (basicComplete == '0') { //基本信息填写不完整
                    var nameZ = /^[\u4E00-\u9FA5\uf900-\ufa2d]{1,10}·?[\u4E00-\u9FA5\uf900-\ufa2d]{1,10}$/;
                    var nameVal = $.trim($('#base_name').val());
                    var nameCorrect = (nameVal != '' && nameZ.test(nameVal));
                    var idcardVal = $('#base_card').val();
                    var validator = new IDValidator();
                    var res = validator.isValid(idcardVal);
                    var idcardCorrect = (idcardVal != '' && res);
                    var cityVal = $('#base_city').val();
                    $(window).scrollTop(0);
                    if (!nameCorrect) {
                        global.popupCover({
                            content: '请输入正确姓名'
                        });
                        $('#base_info .basic_name').addClass('error_pro');
                        return;
                    }
                    else if (!idcardCorrect) {
                        global.popupCover({
                            content: '身份证号格式错误'
                        });
                        $('#base_info .basic_card').addClass('error_pro');
                        return;
                    }
                    else if (cityVal == '') {
                        global.popupCover({
                            content: '请选择城市'
                        });
                        $('#base_info .basic_city').addClass('error_pro');
                        return;
                    }
                }
                else if (jobComplete == '0') { //工作信息填写不完整
                    var jobSel = false;
                    $(window).scrollTop(292);
                    for (var i = 0; i < $('#occupation span').length; i++) {
                        if ($('#occupation span:eq(' + i + ')').hasClass('onSelect')) {
                            jobSel = true;
                            break;
                        }
                    }
                    if (!jobSel) {
                        global.popupCover({
                            content: '请选择职业信息'
                        });
                        $('#occupation').parents('.sel_area').find('.sel_name').addClass('error_pro');
                        return;
                    }
                    else {
                        var jobSelIndex = $('#occupation span.onSelect').index();
                        for (var i = 0; i < $('#job_info .sel_section:eq(' + jobSelIndex + ') .sel_area').length; i++) {
                            if ($('#job_info .sel_section:eq(' + jobSelIndex + ') .sel_area:eq(' + i + ') .sel_option').find('span.onSelect').length == 0) {
                                global.popupCover({
                                    content: '职业信息输入不完整'
                                });
                                $('#job_info .sel_section:eq(' + jobSelIndex + ') .sel_area:eq(' + i + ') .sel_name').addClass('error_pro');
                                break;
                                return;
                            }
                        }
                    }
                }
                else if (assetComplete == '0') { //资产信息填写不完整
                    if ($('#has_insurance .sel_option').find('span.onSelect').length == 0) {
                        global.popupCover({
                            content: '请选择保单'
                        });
                        $('#has_insurance .sel_name').addClass('error_pro');
                        return;
                    }
                    else if ($('#house_info .sel_option').find('span.onSelect').length == 0) {
                        global.popupCover({
                            content: '请选择房产信息'
                        });
                        $('#house_info .sel_name').addClass('error_pro');
                        return;
                    }
                    else if ($('#car_info .sel_option').find('span.onSelect').length == 0) {
                        global.popupCover({
                            content: '请选择汽车信息'
                        });
                        $('#car_info .sel_name').addClass('error_pro');
                        return;
                    }
                }
                else if (creditComplete == '0') { //信用信息填写不完整
                    if ($('#has_creditcard .sel_option').find('span.onSelect').length == 0) {
                        global.popupCover({
                            content: '请选择信用卡信息'
                        });
                        $('#has_creditcard .sel_name').addClass('error_pro');
                        return;
                    }
                    else if ($('#is_micro .sel_option').find('span.onSelect').length == 0) {
                        global.popupCover({
                            content: '请选择微粒贷信息'
                        });
                        $('#is_micro .sel_name').addClass('error_pro');
                        return;
                    }
                }
                else { //当基础信息、工作信息、资产信息、信用信息输入都成功通过时
                    //通过身份证号获取性别及年龄信息
                    global.getCardInfo($('#base_card').val());
                    var salary_extend = $('#occupation span.onSelect').data('val').toString() == '001' ? $('#office_workers .salary_extend .sel_option span.onSelect').data('val').toString() : '', //                  salary = $('#occupation span.onSelect').data('val').toString() == '001' ? $('#office_workers .salary .sel_option span.onSelect').data('val').toString() : $('#occupation span.onSelect').data('val').toString() == '002' ? $('#servant .salary .sel_option span.onSelect').data('val').toString() : $('#private_business_owner .salary .sel_option span.onSelect').data('val').toString(),
                        work_hours = $('#occupation span.onSelect').data('val').toString() == '001' ? $('#office_workers .work_hours .sel_option span.onSelect').data('val').toString() : $('#occupation span.onSelect').data('val').toString() == '002' ? $('#servant .work_hours .sel_option span.onSelect').data('val').toString() : ''
                        , accumulation_fund = $('#occupation span.onSelect').data('val').toString() == '001' ? $('#office_workers .accumulation_fund .sel_option span.onSelect').data('val').toString() : $('#occupation span.onSelect').data('val').toString() == '002' ? $('#servant .accumulation_fund .sel_option span.onSelect').data('val').toString() : ''
                        , social_security = $('#occupation span.onSelect').data('val').toString() == '001' ? $('#office_workers .social_security .sel_option span.onSelect').data('val').toString() : $('#occupation span.onSelect').data('val').toString() == '002' ? $('#servant .social_security .sel_option span.onSelect').data('val').toString() : $('#private_business_owner .social_security .sel_option span.onSelect').data('val').toString()
                        , business_licence = $('#occupation span.onSelect').data('val').toString() == '003' ? $('#private_business_owner .business_licence .sel_option span.onSelect').data('val').toString() : '';
                    if ($('#occupation span.onSelect').data('val').toString() == '001' && $('#office_workers .salary .sel_option span.onSelect').data('val') != undefined) {
                        salary = $('#office_workers .salary .sel_option span.onSelect').data('val').toString();
                    }
                    else if ($('#occupation span.onSelect').data('val').toString() == '002' && $('#servant .salary .sel_option span.onSelect').data('val') != undefined) {
                        salary = $('#servant .salary .sel_option span.onSelect').data('val').toString();
                    }
                    else if ($('#occupation span.onSelect').data('val').toString() == '003' && $('#private_business_owner .salary .sel_option span.onSelect').data('val') != undefined) {
                        salary = $('#private_business_owner .salary .sel_option span.onSelect').data('val').toString();
                    }
                    else {
                        salary = '001';
                    }
                    var postData = {
                        'money': $('#basic-money').val()
                        , 'name': $('#base_name').val()
                        , 'sex': global.sex
                        , 'birthday': global.age
                        , 'certificate_no': $('#base_card').val()
                        , 'city': $('#base_city').val()
                        , 'occupation': $('#occupation span.onSelect').data('val').toString()
                        , 'salary_extend': salary_extend
                        , 'salary': salary
                        , 'work_hours': work_hours
                        , 'accumulation_fund': accumulation_fund
                        , 'social_security': social_security
                        , 'business_licence': business_licence
                        , 'has_insurance': $('#has_insurance .sel_option span.onSelect').data('val')
                        , 'house_info': $('#house_info .sel_option span.onSelect').data('val').toString()
                        , 'car_info': $('#car_info .sel_option span.onSelect').data('val').toString()
                        , 'has_creditcard': $('#has_creditcard .sel_option span.onSelect').data('val')
                        , 'is_micro': $('#has_creditcard .sel_option span.onSelect').data('val')
                    }
                    var _self = this;
                    var token = $('.token').text() || '';
                    if (token == '') {
                        global.sdLogin();
                    }
                    else {
                        global.addLoading();
                        $.ajax({
                            url: api_sudaizhijia_host + "/oneloan/v1/spread/full"
                            , type: "post"
                            , dataType: "json"
                            , data: postData
                            , success: function (result) {
                                if (result.code == 200 && result.error_code == 0) {
                                    $.get(api_sudaizhijia_host + "/view/oneloan/products", {}, function (result) {
                                        $(window).scrollTop(0);
                                        history.pushState({}, null, './basic?page=result_full');
                                        $('#result-page').html(result);
                                        $('#result-page').show().siblings('div').hide();
                                        global.removeLoading();
                                    })
                                }
                                else {
                                    global.popupCover({
                                        content: result.error_message
                                    });
                                    global.removeLoading();
                                }
                            }
                        })
                    }
                }
            })
            //点击选项标签事件
        $('.sel_option').on('click', 'span', function () {
            var _this = this;
            if ($(_this).hasClass('onSelect')) {
                return;
            }
            else {
                $(_this).parents('.sel_area').find('.sel_name').removeClass('error_pro');
                //当点击工作信息内容 监测输入完整情况
                if ($(_this).parents('#job_info').length != 0 && $(this).parents('#occupation').length == 0) {
                    var selFin = true
                        , jobIndex = $('#occupation span.onSelect').index();
                    $(_this).addClass('onSelect').siblings('span').removeClass('onSelect');
                    for (var i = 0; i < $('#job_info .sel_section:eq(' + jobIndex + ') .sel_area').length; i++) {
                        if ($('#job_info .sel_section:eq(' + jobIndex + ') .sel_area:eq(' + i + ') .sel_option').find('span.onSelect').length == 0) {
                            selFin = false;
                            break;
                        }
                    }
                    if (selFin && !dataController.addPercent.job) {
                        $('#job_info .success_percent').html('<span style="color:#fe5c0d;">已完成</span>');
                        $('#job_info .success_percent').attr('isComplete', '1');
                        var curPercent = parseInt($('.knob').val());
                        $('.knob').val(curPercent + 40).trigger("change");
                        $('#percentNum').html(curPercent + 40);
                        dataController.addPercent.job = true;
                    }
                }
                //当点击资产信息内容  监测输入完整情况
                if ($(_this).parents('#asset_info').length != 0) {
                    var selFin = true;
                    $(_this).addClass('onSelect').siblings('span').removeClass('onSelect');
                    for (var i = 0; i < $('#asset_info .sel_area').length; i++) {
                        if ($('#asset_info .sel_area:eq(' + i + ') .sel_option').find('span.onSelect').length == 0) {
                            selFin = false;
                            break;
                        }
                    }
                    if (selFin && !dataController.addPercent.asset) {
                        $('#asset_info .success_percent').html('<span style="color:#fe5c0d;">已完成</span>');
                        $('#asset_info .success_percent').attr('isComplete', '1');
                        var curPercent = parseInt($('.knob').val());
                        $('.knob').val(curPercent + 25).trigger("change");
                        $('#percentNum').html(curPercent + 25);
                        dataController.addPercent.asset = true;
                    }
                }
                //当点击信用信息内容   监测输入完整情况
                if ($(_this).parents('#credit_info').length != 0) {
                    var selFin = true;
                    $(_this).addClass('onSelect').siblings('span').removeClass('onSelect');
                    for (var i = 0; i < $('#credit_info .sel_area').length; i++) {
                        if ($('#credit_info .sel_area:eq(' + i + ') .sel_option').find('span.onSelect').length == 0) {
                            selFin = false;
                            break;
                        }
                    }
                    if (selFin && !dataController.addPercent.credit) {
                        $('#credit_info .success_percent').html('<span style="color:#fe5c0d;">已完成</span>');
                        $('#credit_info .success_percent').attr('isComplete', '1');
                        var curPercent = parseInt($('.knob').val());
                        $('.knob').val(curPercent + 10).trigger("change");
                        $('#percentNum').html(curPercent + 10);
                        dataController.addPercent.credit = true;
                    }
                }
            }
        });
        //切换工作
        $('#occupation').on('click', 'span', function () {
            if ($(this).hasClass('onSelect')) {
                return;
            }
            else {
                var idx = $(this).index();
                $(this).addClass('onSelect').siblings('span').removeClass('onSelect');
                $('#occupation').parents('.sel_area').find('.sel_name').removeClass('error_pro');
                $('#job_info .job_cover_box').show().find('span').removeClass('onSelect').removeClass('error_pro');
                $('#job_info .success_percent').html('完善工作信息，下款成功率+40%');
                $('#job_info .success_percent').attr('isComplete', '0');
                if (dataController.addPercent.job) {
                    var curPercent = parseInt($('.knob').val());
                    $('.knob').val(curPercent - 40).trigger("change");
                    $('#percentNum').html(curPercent - 40);
                    dataController.addPercent.job = false;
                }
                $('.sel_section').eq(idx).show().siblings('.sel_section').hide();
            }
        });
    }, //计算高度定位底部提交按钮
    //  fixedLoansBtn: function () {
    //      var minHeight = $(window).height() - $('#full_top').height() - $('#full_loans').height()
    //      $('.main').css('min-height', minHeight);
    //  }
}
