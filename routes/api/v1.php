<?php
//test
$router->group(['prefix' => 'v1', 'namespace' => 'V1', 'middleware' => ['sign', 'cros', 'analysis']], function ($router) {
    /**
     *  token 相关
     */
    $router->group(['prefix' => 'token'], function ($router) {
        // 正常登陆
        $router->post('noce2access', ['uses' => 'TokenController@noce2access']);
    });

    /**
     *   Auth API
     */
    $router->group(['prefix' => 'auth'], function ($router) {
        // 正常登陆
        $router->post('login', [/*'middleware' => ['validate:login'],*/ 'uses' => 'AuthController@login']);
        // 快速登陆
        $router->post('quicklogin', ['middleware' => ['validate:quicklogin'], 'uses' => 'AuthController@quickLogin']);
        //快捷注册
        $router->post('quick/register', ['middleware' => ['validate:quicklogin'], 'uses' => 'AuthController@quickRegister']);
        // 用户退出
        $router->post('logout', ['middleware' => ['auth'], 'uses' => 'AuthController@logout']);
    });

    /**
     *  Users API
     */
    $router->group(['prefix' => 'users'], function ($router) {
        //***************** ********************
        //创建（修改）密码
        $router->post('updatepwd', ['middleware' => ['auth', 'validate:updatepwd'], 'uses' => 'UserController@updatePwd']);
        //修改用户名&昵称
        $router->post('updatename', ['middleware' => ['auth', 'validate:updateindent', 'validate:username'], 'uses' => 'UserController@updateUsernameAndIndent']);
        //验证code 修改密码 修改手机号
        $router->get('checkcode', ['middleware' => ['auth', 'validate:resetPhone', 'validate:smstype'], 'uses' => 'UserController@checkMobileCode']);
        //验证code 忘记密码
        $router->get('checkForgetPwdcode', ['middleware' => ['validate:resetPhone', 'validate:smstype'], 'uses' => 'UserController@checkMobileCode']);
        //修改手机号
        $router->post('updateMobile', ['middleware' => ['auth', 'validate:resetPhone'], 'uses' => 'UserController@updateMobile']);
        //忘记密码
        $router->post('forgetPwd', ['middleware' => ['validate:code', 'validate:updatepwd'], 'uses' => 'UserController@forgetPwd']);

        //***************** 优化url接口访问路径 ********************
        //创建（修改）密码
        $router->post('password', ['middleware' => ['auth', 'validate:updatepwd'], 'uses' => 'UserController@updatePwd']);
        //修改用户名&昵称
        $router->post('username', ['middleware' => ['auth', 'api_cros', 'validate:updateindent', 'validate:username'], 'uses' => 'UserController@updateUsernameAndIndent']);
        //验证code 修改密码 修改手机号
        $router->get('code', ['middleware' => ['auth', 'validate:resetPhone', 'validate:smstype'], 'uses' => 'UserController@checkMobileCode']);
        //验证code 忘记密码
        $router->get('password/code', ['middleware' => ['validate:resetPhone', 'validate:smstype'], 'uses' => 'UserController@checkMobileCode']);
        //修改手机号
        $router->post('mobile', ['middleware' => ['auth', 'validate:resetPhone'], 'uses' => 'UserController@updateMobile']);
        //忘记密码
        $router->post('password/forget', ['middleware' => ['validate:code', 'validate:updatepwd'], 'uses' => 'UserController@forgetPwd']);
        //修改身份
        $router->post('identity', ['middleware' => ['validate:identity', 'auth'], 'uses' => 'UserController@updateIdentity']);
        //用户账户信息
        $router->get('info', ['middleware' => ['auth'], 'uses' => 'UserController@fetchUserinfo']);
        //上传用户头像
        $router->post('photo', ['middleware' => ['auth', 'validate:photo'], 'uses' => 'UserController@uploadPhoto']);
        //***************** 优化url接口访问路径 ********************


        $router->post('', ['middleware' => ['auth'], 'uses' => 'UserController@create']);
        $router->put('{id}', ['middleware' => ['auth'], 'uses' => 'UserController@update']);
        $router->delete('{id}', ['middleware' => ['auth'], 'uses' => 'UserController@delete']);
        $router->get('', ['middleware' => ['auth'], 'uses' => 'UserController@index']);


        //身份验证
        $router->group(['prefix' => 'identity'], function ($router) {
            // 调用face++
            $router->group(['prefix' => 'faceid'], function ($router) {
                // 检测和识别中华人民共和国第二代身份证正面
                $router->post('ocridcard/front', ['middleware' => ['auth', 'validate:idcardFront'], 'uses' => 'UserIdentityController@fetchFaceidToCardfrontInfo']);
                // by xuyj v3.2.3
                $router->post('ocridcard/front_new', ['middleware' => ['auth', 'validate:idcardFront'], 'uses' => 'UserIdentityController@fetchFaceidToCardfrontInfo_new']);
                // 检测和识别中华人民共和国第二代身份证反面
                $router->post('ocridcard/back', ['middleware' => ['auth', 'validate:idcardBack'], 'uses' => 'UserIdentityController@fetchFaceidToCardbackInfo']);
                //修改face返回的用户信息
                $router->post('info', ['middleware' => ['auth', 'validate:faceUserinfo'], 'uses' => 'UserIdentityController@updateUserRealnameByIdcardFront']);
                //活体认证
                $router->post('alive', ['middleware' => ['auth', 'validate:alive'], 'uses' => 'UserIdentityController@verifyFaceidToIdcard']);
            });

            //调用天创
            $router->group(['prefix' => 'tcredit'], function ($router) {
                //天创验证身份证合法信息
                $router->post('', ['middleware' => ['auth'], 'uses' => 'UserIdentityController@checkIdcardFromTianchuang']);
            });

            //修改实名认证
            $router->post('certifica', ['middleware' => ['auth', 'validate:verifyMobileInfo3'], 'uses' => 'UserIdentityController@updateRealnameInfo']);
            //查询实名认证
            $router->get('certifica', ['middleware' => ['auth'], 'uses' => 'UserIdentityController@fetchRealnameAndIdcard']);
            //百款聚到 - 修改实名认证
            $router->post('oneloan/certifica', ['middleware' => ['auth', 'validate:verifyMobileInfo3', 'validate:nid'], 'uses' => 'UserIdentityController@updateFakeRealnameInfo']);

        });

        // 用户银行卡绑定
        $router->group(['prefix' => 'payment'], function ($router) {
            $router->group(['prefix' => 'card'], function ($router) {
                // 添加新的银行卡
                $router->post('', ['middleware' => ['auth', 'validate:cardType', 'validate:mobile', 'validate:bank'], 'uses' => 'UserBankCardController@createOrUpdateUserBanksById']);
                // 更换银行卡
                $router->post('replace', ['middleware' => ['auth', 'validate:bankReplace'], 'uses' => 'UserBankCardController@createOrUpdateUserBanksById']);
                //绑定银行列表
                $router->get('banks', ['middleware' => ['auth', 'validate:cardType'], 'uses' => 'UserBankCardController@fetchUserBanks']);
                //删除银行卡
                $router->delete('', ['middleware' => ['auth', 'validate:cardType', 'validate:userbankId'], 'uses' => 'UserBankCardController@deleteUserBankById']);
                //修改银行卡默认状态
                $router->post('default', ['middleware' => ['auth', 'validate:userbankId'], 'uses' => 'UserBankCardController@updateBankcardDefaultById']);
                //开卡银行信息
                $router->get('verify', ['middleware' => ['auth', 'validate:bank', 'validate:cardType'], 'uses' => 'UserBankCardController@checkBankCardNum']);
                //修改支付银行卡
                $router->post('pay/status', ['middleware' => ['auth', 'validate:userbankId'], 'uses' => 'UserBankCardController@updateCardLastStatus']);
                // 信用卡 —— 支持银行及限额
                $router->get('quota/credit', ['uses' => 'UserBankCardController@fetchQuotaCreditCardBanks']);
                // 储蓄卡 —— 支持银行及限额
                $router->get('quota/saving', ['uses' => 'UserBankCardController@fetchQuotaSavingCardBanks']);
                // 验证用户是否绑定银行卡
                $router->get('check', ['middleware' => ['auth'], 'uses' => 'UserBankCardController@checkUserBank']);
            });

            //支付订单
            $router->group(['prefix' => 'orders'], function ($router) {
                //创建订单-新 by xuyj
                $router->post('new', ['middleware' => ['auth', 'validate:userVipOrder'], 'uses' => 'PaymentController@fetchOrder_new']);
                // 提前知道 使用哪种支付通道
                $router->post('choicepaychnl', ['middleware' => ['auth', 'validate:userVipOrder'], 'uses' => 'PaymentController@choicepaychannel']);
                // 汇聚快捷支付 短信签约确认 by xuyj
                $router->post('sign', ['middleware' => ['auth', 'validate:userVipOrder'], 'uses' => 'PaymentController@huiJuSign']);
                //创建订单
                $router->post('', ['middleware' => ['auth', 'validate:userVipOrder'], 'uses' => 'PaymentController@fetchOrder']);
                //确认支付描述
                $router->get('product/info', ['middleware' => ['auth', 'validate:orderType'], 'uses' => 'PaymentController@fetchOrderInfo']);
                //确认支付描述--新 by xuyj
                $router->get('product/info_new', ['middleware' => ['auth', 'validate:orderType'], 'uses' => 'PaymentController@fetchOrderInfo_new']);
                //验证订单是否支付成功
                $router->get('status', ['middleware' => ['auth', 'validate:orderNum'], 'uses' => 'PaymentController@fetchPaymentStatus']);
                $router->get('status_new', ['middleware' => ['auth', 'validate:orderNum'], 'uses' => 'PaymentController@fetchPaymentStatus_new']);


            });

        });

        // VIP
        $router->group(['prefix' => 'vip'], function ($router) {
            $router->get('', ['middleware' => ['auth'], 'uses' => 'UserVipController@info']);
            //会员特权
            $router->get('previlege', ['middleware' => ['auth'], 'uses' => 'UserVipController@previlege']);
            //会员中心-普通用户
            $router->get('members', ['middleware' => ['auth'], 'uses' => 'UserVipController@memberHome']);
            //银行列表
            $router->get('banks', ['middleware' => ['auth'], 'uses' => 'UserVipController@getBankList']);
            //银行列表 by xuyj  v3.2.3
            $router->get('banks_new', ['middleware' => ['auth'], 'uses' => 'UserVipController@getBankList_new']);
            //会员中心
            $router->get('centers', ['uses' => 'UserVipController@memberCenter']);
            //会员充值金额
            $router->get('recharge', ['uses' => 'UserVipController@fetchVipRecharge']);
            //会员动态-虚值
            $router->get('virtual', ['uses' => 'UserVipController@fetchMembershipDynamics']);
            //特权跳转地址
            $router->get('oauth/previlege', ['uses' => 'UserVipController@fetchPrivilegeUrl']);
            //会员购买类型
            $router->post('member/bay', ['uses' => 'MemberPurchaseController@fetchPurchaseType']);
            //会员中心来源
            $router->post('member/center', ['uses' => 'MemberPurchaseController@fetchMemberCenter']);
        });

        //信用报告
        $router->group(['prefix' => 'report'], function ($router) {
            //首页图片
            $router->get('banner', ['uses' => 'UserReportController@fetchBanner']);
            //免费查
            $router->get('free', ['middleware' => ['auth'], 'uses' => 'UserReportController@fetchFree']);
            //付费查
            $router->get('pay', ['middleware' => ['auth'], 'uses' => 'UserReportController@fetchPay']);
            //生成免费报告 && 获取用户认证信息
            $router->get('zhima/info', ['middleware' => ['auth', 'validate:payType'], 'uses' => 'UserReportController@fetchZhimaUserinfo']);
            //芝麻跳转地址
            $router->get('zhima/route', ['middleware' => ['auth', 'validate:payType'], 'uses' => 'UserReportController@fetchZhimaRoute']);
            //前端轮循处理 查询芝麻处理状态
            $router->post('zhima/step', ['middleware' => ['auth', 'validate:payType'], 'uses' => 'UserReportController@fetchZhimaStep']);
            //前端轮循处理 修改运营商SDK状态
            $router->post('carrier/step', ['middleware' => ['auth', 'validate:payType'], 'uses' => 'UserReportController@createOrUpdateTask']);
            //报告生成中状态
            $router->get('step', ['middleware' => ['auth', 'validate:payType'], 'uses' => 'UserReportController@fetchProducting']);
            //信用报告支付查 判断支付查询结果
            $router->get('check', ['middleware' => ['auth'], 'uses' => 'UserReportController@checkStatusById']);
            //信用报告订单列表
            $router->get('reports', ['middleware' => ['auth'], 'uses' => 'UserReportController@fetchReports']);
            // 验证单个报告进行步骤
            $router->get('check/step', ['middleware' => ['auth', 'validate:payType', 'validate:reportTaskId'], 'uses' => 'UserReportController@checkReportStatusById']);
            // 信用报告 需要用户信息
            $router->get('user/info', ['middleware' => ['auth'], 'uses' => 'UserReportController@fetchReportUserinfo']);

        });

        //用户账单平台
        $router->group(['prefix' => 'bill/platform'], function ($router) {

            //删除账单平台
            $router->delete('', ['middleware' => ['auth', 'validate:billPlatformId'], 'uses' => 'UserBillPlatformController@deleteBillPlatform']);
            //修改还款提醒状态
            $router->post('status', ['middleware' => ['auth', 'validate:billPlatformStatus'], 'uses' => 'UserBillPlatformController@updateStatus']);

            //用户信用卡平台
            $router->group(['prefix' => 'banks'], function ($router) {
                //信用报告银行列表
                $router->get('', ['uses' => 'UserBillPlatformController@fetchBillBanks']);
                //修改前查询信用卡信息
                $router->get('creditcard', ['middleware' => ['auth', 'validate:billCreditcardId'], 'uses' => 'UserBillPlatformController@fetchCreditcardInfo']);
                //判断是否可以添加信用卡&导入信用卡数据
                $router->get('check', ['middleware' => ['auth'], 'uses' => 'UserBillPlatformController@fetchCreditcardSign']);
                //创建或修改信用卡
                $router->post('creditcard', ['middleware' => ['auth', 'validate:billCreditcard'], 'uses' => 'UserBillPlatformController@createOrUpdateCreditcard']);
                //信用卡平台列表
                $router->get('creditcards', ['middleware' => ['auth'], 'uses' => 'UserBillPlatformController@fetchCreditcards']);
                //账单管理——信用卡列表
                $router->get('manages', ['middleware' => ['auth'], 'uses' => 'UserBillPlatformController@fetchCreditcardManages']);

            });

            //用户网贷平台
            $router->group(['prefix' => 'product'], function ($router) {
                //网贷平台列表
                $router->get('products', ['uses' => 'UserBillPlatformController@fetchProducts']);
                //添加或修改网贷平台数据
                $router->post('', ['middleware' => ['auth', 'validate:billProduct'], 'uses' => 'UserBillPlatformController@createOrUpdateProduct']);
                //单个产品详情
                $router->get('', ['middleware' => ['auth', 'validate:billProductId'], 'uses' => 'UserBillPlatformController@fetchProduct']);
                //网贷详情
                $router->get('info', ['middleware' => ['auth', 'validate:billProductId'], 'uses' => 'UserBillPlatformController@fetchProductInfo']);
                //网贷详情统计
                $router->get('info/count', ['middleware' => ['auth', 'validate:billProductId'], 'uses' => 'UserBillPlatformController@fetchProductInfoCount']);
                //账单管理——网贷列表
                $router->get('manages', ['middleware' => ['auth'], 'uses' => 'UserBillPlatformController@fetchManageProducts']);
            });

        });

        //用户产品账单&网贷产品
        $router->group(['prefix' => 'bill'], function ($router) {

            //账单设为已还
            $router->post('status', ['middleware' => ['auth', 'validate:billId'], 'uses' => 'UserBillController@updateBillStatus']);
            //图表分析
            $router->get('analysis', ['middleware' => ['auth'], 'uses' => 'UserBillController@fetchBillAnalysis']);

            //用户信用卡账单
            $router->group(['prefix' => 'banks'], function ($router) {
                //首页账单总量统计
                $router->get('count', ['middleware' => ['auth'], 'uses' => 'UserBillController@fetchCreditcardCount']);
                //首页账单列表
                $router->get('bills', ['middleware' => ['auth'], 'uses' => 'UserBillController@fetchCreditcardUserbills']);
                //添加信用卡账单
                $router->post('creditcard', ['middleware' => ['auth', 'validate:bill'], 'uses' => 'UserBillController@createOrUpdateCreditcardBill']);
                //某平台下账单明细
                $router->get('creditcard/bills', ['middleware' => ['auth', 'validate:billCreditcardId'], 'uses' => 'UserBillController@fetchCreditcardBills']);
                //账单明细详情
                $router->get('creditcard/detail', ['middleware' => ['auth', 'validate:billId'], 'uses' => 'UserBillController@fetchCreditcardBillDetails']);
                //账单导入网银列表
                $router->get('import/banks', ['middleware' => ['auth'], 'uses' => 'UserBillController@fetchImportTypeData']);
                //账单采集步骤通知
                $router->get('import/step', ['middleware' => ['auth'], 'uses' => 'UserBillController@fetchBillInfoStatus']);

            });

            //用户网贷平台
            $router->group(['prefix' => 'product'], function ($router) {
                //修改网贷金额
                $router->post('money', ['middleware' => ['auth', 'validate:billMoney'], 'uses' => 'UserBillController@updateProductBillMoney']);
                //首页网贷列表
                $router->get('bills', ['middleware' => ['auth'], 'uses' => 'UserBillController@fetchHomeProducts']);
            });


        });

        //连登解锁
        $router->group(['prefix' => 'unlock/login'], function ($router) {
            //连登
            $router->post('', ['middleware' => ['auth'], 'uses' => 'UserUnlockLoginController@createUserUnlockLogin']);
        });

    });


    /**
     *  Sms API
     */
    $router->group(['prefix' => 'sms'], function ($router) {
        //注册短信验证码
        $router->post('register', ['middleware' => ['validate:code'], 'uses' => 'SmsController@register']);
        //修改密码短信验证码
        $router->post('password', ['middleware' => ['validate:code'], 'uses' => 'SmsController@password']);
        //登录短信验证码
        $router->post('login', ['middleware' => ['validate:code'], 'uses' => 'SmsController@login']);
        //手机号发送短信
        $router->post('phone', ['middleware' => ['validate:code'], 'uses' => 'SmsController@phone']);
        //***************** ********************
        //修改手机号短发送短信
        $router->post('updatePhone', ['middleware' => ['validate:code'], 'uses' => 'SmsController@updatePhone']);
        //忘记密码
        $router->post('forgetPwd', ['middleware' => ['validate:code'], 'uses' => 'SmsController@forgetPwd']);

        //***************** 优化url接口访问路径 ********************
        //修改手机号短发送短信
        $router->post('phone/update', ['middleware' => ['validate:code'], 'uses' => 'SmsController@updatePhone']);
        //忘记密码
        $router->post('password/forget', ['middleware' => ['validate:code'], 'uses' => 'SmsController@forgetPwd']);
        //***************** 优化url接口访问路径 ********************

        //落地页根据手机号是否注册弹窗
        $router->post('check', ['middleware' => ['cros', 'validate:code'], 'uses' => 'SmsController@check']);
    });

    /**
     *  Userinfo API
     */
    $router->group(['prefix' => 'userinfo'], function ($router) {
        //基础信息 —— 查询用户基础信息
        $router->get('basic', ['middleware' => ['auth', 'api_cros'], 'uses' => 'UserinfoController@fetchBasicinfo']);
        //基础信息 —— 修改用户基础信息
        $router->post('basic', ['middleware' => ['auth', 'api_cros'], 'uses' => 'UserinfoController@updateBasicinfo']);
        //信用信息 —— 查询用户信用信息
        $router->get('identity', ['middleware' => ['auth', 'api_cros'], 'uses' => 'UserinfoController@fetchIdentityinfo']);
        //信用信息 —— 修改用户信用信息
        $router->post('identity', ['middleware' => ['auth', 'api_cros'], 'uses' => 'UserinfoController@updateIdentityinfo']);
        //审核资料 —— 查询用户审核资料
        $router->get('certify', ['middleware' => ['auth', 'api_cros'], 'uses' => 'UserinfoController@fetchCertifyinfo']);
        //审核资料 —— 修改用户审核资料
        $router->post('certify', ['middleware' => ['auth', 'api_cros'], 'uses' => 'UserinfoController@updateCertifyinfo']);

    });

    /**
     *  Favourite API
     */
    $router->group(['prefix' => 'favourite'], function ($router) {
        //单独判断产品是否收藏+添加、修改评论
        $router->get('collection', ['middleware' => ['auth', 'validate:productdetail'], 'uses' => 'FavouriteController@fetchCollections']);
        //产品——收藏列表
        $router->get('collectionlists', ['middleware' => ['auth'], 'uses' => 'FavouriteController@fetchCollectionLists']);
        //产品——添加收藏
        $router->post('collection', ['middleware' => ['auth', 'validate:productdetail'], 'uses' => 'FavouriteController@createCollectionById']);
        //产品——删除收藏
        $router->delete('collection', ['middleware' => ['auth', 'validate:productdetail'], 'uses' => 'FavouriteController@deleteCollectionById']);
        //资讯——收藏列表
        $router->get('newslists', ['middleware' => ['auth'], 'uses' => 'FavouriteController@fetchCollectionNewsLists']);
        //资讯——添加收藏
        $router->post('newscollection', ['middleware' => ['auth', 'validate:newsdetail'], 'uses' => 'FavouriteController@createCollectionNewsById']);
        //资讯——删除收藏
        $router->delete('newscollection', ['middleware' => ['auth', 'validate:newsdetail'], 'uses' => 'FavouriteController@deleteCollectionNewsById']);

    });

    /**
     *  Comment API
     */
    $router->group(['prefix' => 'comment'], function ($router) {
        //评论体验分
        $router->get('score', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchCommentScore']);
        //评论列表
        $router->get('lists', ['middleware' => ['validate:commentlist', 'validate:page'], 'uses' => 'CommentController@fetchCommentLists']);
        //查询评论内容
        $router->get('', ['middleware' => ['validate:productdetail', 'auth'], 'uses' => 'CommentController@fetchCommentDatas']);
        //修改评论内容
        $router->post('', ['middleware' => ['validate:comment', 'auth'], 'uses' => 'CommentController@updateCommentDatas']);
        //点赞
        $router->post('useful', ['middleware' => ['validate:commentuseful', 'auth'], 'uses' => 'CommentController@createCommentUserful']);
        //取消点赞
        $router->delete('useful', ['middleware' => ['validate:commentuseful', 'auth'], 'uses' => 'CommentController@deleteCommentUserful']);
        //评论 —— 借款状态统计
        $router->get('counts', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchCommentCounts']);
    });

    /**
     *  Reply API
     */
    $router->group(['prefix' => 'reply'], function ($router) {
        //创建回复
        $router->post('', ['middleware' => ['auth', 'validate:reply'], 'uses' => 'ReplyController@createReply']);
        //回复列表
        $router->get('replys', ['middleware' => ['validate:replys'], 'uses' => 'ReplyController@fetchReplysByCommentId']);
        //多级回复  回复点赞
        $router->post('useful', ['middleware' => ['validate:replyId', 'auth'], 'uses' => 'ReplyController@replyClickuseful']);
        //多级回复 取消点赞
        $router->delete('useful', ['middleware' => ['validate:replyId', 'auth'], 'uses' => 'ReplyController@deleteReplyClickuseful']);
    });

    /**
     *  Versions API
     */
    $router->group(['prefix' => 'versions'], function ($router) {
        //android 版本升级
        $router->get('android', ['middleware' => ['validate:upgrade'], 'uses' => 'VersionController@upgradeAndroid']);
        //ios 版本升级
        $router->get('ios', ['middleware' => ['validate:upgrade'], 'uses' => 'VersionController@upgradeIos']);
    });

    /**
     *  Banners API
     */
    $router->group(['prefix' => 'banners'], function ($router) {
        $router->get('', ['uses' => 'BannersController@banners']);
        //广告点击流水
        $router->post('', ['middleware' => ['validate:bannerId'], 'uses' => 'BannersController@createBannerLog']);
        //分类专题
        $router->get('special', ['middleware' => ['validate:banners'], 'uses' => 'BannersController@fetchSpecials']);
        //速贷推荐
        $router->get('recommend', ['uses' => 'BannersController@fetchRecommends']);
        //banner跳转资讯详情
        $router->get('new', ['middleware' => ['validate:newsdetail'], 'uses' => 'BannersController@fetchNewinfoById']);
        //启动页广告
        $router->get('advertisement', ['uses' => 'BannersController@launchAdvertisement']);
        //速贷推荐
        $router->get('subjects', ['uses' => 'BannersController@fetchSubjects']);
        //账单导入轮播
        $router->get('bill/import', ['uses' => 'BannersController@fetchBillBanners']);
        //会员中心广告
        $router->get('vip/center', ['uses' => 'BannersController@fetchVipCenterBanner']);

        //置顶分类专题
        $router->get('special/tops', ['uses' => 'BannersController@fetchSpecialTops']);
        //异形广告
        $router->get('special/shaped', ['uses' => 'BannersController@fetchSpecialShapedBanners']);
        //极速贷
        $router->get('quickloan', ['uses' => 'BannersController@fetchQuickLoanBanners']);
        //置顶推荐
        $router->get('recommend/tops', ['uses' => 'BannersController@fetchRecommendTops']);
        //广告解锁连登
        $router->get('unlock', ['uses' => 'BannersController@fetchBannerUnlockLogins']);
        //广告解锁连登流水
        $router->post('unlock/log', ['middleware' => ['auth'], 'uses' => 'BannersController@createBannerUnlockLoginLog']);

    });

    /**
     *  Product API
     */
    $router->group(['prefix' => 'product'], function ($router) {
        //诱导轮播
        $router->get('promotion', ['uses' => 'ProductController@fetchPromotions']);
        //新上线产品
        $router->get('online', ['uses' => 'ProductController@fetchNewOnlines']);
        //专题产品
        $router->get('special', ['middleware' => ['validate:special'], 'uses' => 'ProductController@fetchSpecials']);
        //计算器
        $router->get('calculator', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchCalculators']);
        //产品详情
        $router->get('detail', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetails']);
        //产品列表 && 搜索产品
        $router->get('lists', ['middleware' => ['validate:search'], 'uses' => 'ProductController@fetchProductOrSearch']);
        //产品搜索标签
        $router->get('searchtag', ['uses' => 'ProductController@fetchProductTagConfig']);
        //首页推荐产品
        $router->get('recommends', ['uses' => 'ProductController@fetchRecommends']);
        //代还信用卡产品
        $router->get('givebacks', ['uses' => 'ProductController@fetchGiveBackProducts']);
        //还款提醒中的推荐产品
        $router->get('accounts', ['uses' => 'ProductController@fetchAccountAlertProducts']);
        //我的申请 —— 贷款
        $router->get('historys', ['middleware' => ['auth'], 'uses' => 'ProductController@fetchApplyHistory']);
        //不想看产品列表
        $router->get('blacks', ['middleware' => ['auth'], 'uses' => 'ProductController@fetchProductBlacks']);
        //添加不想看产品
        $router->post('black', ['middleware' => ['auth', 'validate:productdetail'], 'uses' => 'ProductController@updateProductBlack']);
        //删除不想看产品
        $router->delete('black', ['middleware' => ['auth', 'validate:productdetail'], 'uses' => 'ProductController@deleteProductBlack']);
        //首页ROI排序产品
        $router->get('roi', ['uses' => 'ProductController@fetchRoiProducts']);
        //首页
        $router->get('counts', ['uses' => 'ProductController@fetchProductCounts']);
        //账单——推荐产品
        $router->get('bill/specials', ['middleware' => ['validate:search'], 'uses' => 'ProductController@fetchBillProductSpecials']);
        //速贷大全 —— 会员申请产品统计
        $router->get('vip/count', ['uses' => 'ProductController@fetchUserVipProductCount']);
        //滑动专题
        $router->get('slides', ['uses' => 'ProductController@fetchSlideSpecials']);
        //不想看产品标签
        $router->get('black/tags', ['uses' => 'ProductController@fetchProductBlackTags']);
        //不想看标签添加
        $router->post('black/tags', ['middleware' => ['auth', 'validate:productBlackTag'], 'uses' => 'ProductController@createProductBlackTag']);
        //H5注册链接地址
        $router->get('web/url', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchProductUrlByProductId']);
        //一键选贷款推荐产品
        $router->get('oneloan', ['uses' => 'ProductController@fetchOneloanProducts']);
        //banner推荐产品
        $router->get('banner/special', ['uses' => 'ProductController@fetchBannerSpecials']);
        //立即申请 - 推荐标签匹配产品列表
        $router->get('tag/matchs', ['middleware' => ['auth', 'validate:productdetail'], 'uses' => 'ProductController@fetchProductTagMatchs']);
        //极速贷
        $router->get('quickloan', ['uses' => 'ProductController@fetchQuickloanProducts']);
        //合作贷
        $router->get('cooperate', ['uses' => 'ProductController@fetchCooperateProducts']);
        //landing推荐产品
        $router->get('landing', ['uses' => 'ProductController@fetchRecommendLandingProducts']);
        //将轮播产品存入cache中
        $router->post('circulate', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@postProductIdToCache']);


    });

    /**
     *  News API
     */
    $router->group(['prefix' => 'news', 'middleware' => ['validate:page']], function ($router) {
        $router->get('guide', ['uses' => 'NewsController@fetchGuides']);
        $router->get('activity', ['middleware' => ['validate:activity'], 'uses' => 'NewsController@fetchActivities']);
        $router->get('detail', ['middleware' => ['validate:newsdetail'], 'uses' => 'NewsController@fetchDetails']);
    });

    /**
     *  Credit API
     */
    $router->group(['prefix' => 'credit'], function ($router) {
        $router->get('transaction', ['uses' => 'CreditController@transaction']);
        //积分首页
        $router->get('index', ['middleware' => ['auth'], 'uses' => 'CreditController@fetchCreditIndexs']);
        // 获取我的积分信息
        $router->get('', ['middleware' => ['validate:credit', 'auth'], 'uses' => 'CreditController@fetchCredits']);
        // 获取用户积分
        $router->get('cash', ['middleware' => ['auth'], 'uses' => 'CreditController@fetchCash']);
        //积分兑现金  兑换
        $router->post('creditcash', ['middleware' => ['validate:creditCash', 'auth'], 'uses' => 'CreditController@createCreditCash']);
        //积分产品申请
        $router->post('apply', ['middleware' => ['validate:productdetail', 'auth'], 'uses' => 'CreditController@createApply']);
        //催审扣积分
        $router->post('urge', ['middleware' => ['auth', 'validate:urgeId'], 'uses' => 'CreditController@reduceCreditsByUrge']);
        //赚积分
        $router->get('increase', ['middleware' => ['auth'], 'uses' => 'CreditController@fetchAddIntegrals']);
        //赚积分列表页面
        $router->get('increase', ['middleware' => ['auth'], 'uses' => 'CreditController@fetchAddIntegrals']);
        //积分商城
        $router->get('shop', ['uses' => 'CreditShopController@shop']);
    });

    /**
     *  Account API
     */
    $router->group(['prefix' => 'account'], function ($router) {
        // 获取我的账号信息&&账户流水
        $router->get('', ['middleware' => ['auth'], 'uses' => 'AccountController@fetchMyAccountAndLog']);
        //账户提现  查询
        $router->get('useraccount', ['middleware' => ['auth'], 'uses' => 'AccountController@fetchUserAccounts']);
        //账户提现  提现
        $router->post('cash', ['middleware' => ['validate:account', 'auth'], 'uses' => 'AccountController@updateUserAccounts']);
        //提现规则
        $router->get('rule', ['uses' => 'AccountController@getRules']);
    });

    /**
     * Invite API
     */
    $router->group(['prefix' => 'invite'], function ($router) {
        // 用户邀请信息
        $router->get('', ['middleware' => ['auth'], 'uses' => 'InviteController@fetchInvites']);
        //生成二维码
        $router->get('qrcode', ['middleware' => ['validate:qrcode', 'auth'], 'uses' => 'InviteController@fetchQrcode']);
        //邀请流水
        $router->get('log', ['middleware' => ['auth'], 'uses' => 'InviteController@fetchInviteLog']);
        //发短信邀请好友
        $router->post('smsinvite', ['middleware' => ['validate:smsinvite', 'auth'], 'uses' => 'InviteController@createSmsInvite']);
    });

    /**
     *  Config API
     */
    $router->group(['prefix' => 'config'], function ($router) {
        //过审
        $router->group(['prefix' => 'ios'], function ($router) {
            $router->get('pending', ['uses' => 'ConfigController@iOSPending']);
        });
        //配置appkey
        $router->get('appkey', ['uses' => 'ConfigController@fetchAppkey']);
    });

    /**
     *  Notice API
     */
    $router->group(['prefix' => 'notice'], function ($router) {
        // 通知列表
        $router->get('notices', ['middleware' => ['auth'], 'uses' => 'NoticeController@fetchNoticeLists']);
    });

    /**
     *  Help API
     */
    $router->group(['prefix' => 'help'], function ($router) {
        // 帮助中心列表
        $router->get('', ['uses' => 'HelpController@fetchHelpLists']);
        // 帮助中心 —— android 调 h5 的帮助中心连接地址
        $router->get('android', ['uses' => 'HelpController@fetchHelpsToAndroid']);
        // 帮助中心 —— 提问&反馈
        $router->post('feedback', ['middleware' => ['auth', 'api_cros', 'validate:feedback'], 'uses' => 'HelpController@createFeedback']);
        // 关于我们
        $router->get('shareours', ['uses' => 'HelpController@fetchShareOurs']);
        //帮助中心分类
        $router->get('types', ['uses' => 'HelpController@fetchHelpTypes']);
        //获取单条帮助分类内容
        $router->get('help', ['middleware' => ['validate:helpTypeId'], 'uses' => 'HelpController@fetchHelpsById']);
    });

    /**
     *  Exact API
     */
    $router->group(['prefix' => 'exact'], function ($router) {
        //精确匹配banner
        $router->get('banner', ['uses' => 'ExactController@fetchExactBanner']);
        //精确匹配数据
        $router->get('data', ['middleware' => ['auth'], 'uses' => 'ExactController@fetchMatchData']);
        //精准匹配判断基础信息是否完整
        $router->get('completeness', ['middleware' => ['auth', 'validate:exact'], 'uses' => 'ExactController@fetchBasicCompleteness']);
        //获取精确匹配数据
        $router->get('exacts', ['middleware' => ['auth', 'validate:page'], 'uses' => 'ExactController@fetchExactMatchDatas']);

    });

    /**
     *  Bank API
     */
    $router->group(['prefix' => 'bank'], function ($router) {
        // 基础信息 —— 银行列表
        $router->get('lists', ['uses' => 'BankController@fetchBankLists']);
        // 基础信息 —— 验证银行名称
        $router->get('validate', ['middleware' => ['validate:bankName'], 'uses' => 'BankController@validateBankName']);
        // 银行列表数据是否更新
        $router->get('count', ['uses' => 'BankController@fetchBankCounts']);
        // 【h5】获取银行名称
        $router->get('bankname', ['middleware' => ['validate:bank'], 'uses' => 'BankController@fetchBankName']);

    });

    /**
     *  Push API
     */
    $router->group(['prefix' => 'push'], function ($router) {
        // 极光推送 —— 接收用户指定设备的registrationId
        $router->get('registration', ['middleware' => ['auth', 'validate:push'], 'uses' => 'PushController@putRegistrationIdToCache']);
        //任务弹窗
        $router->get('popup', ['middleware' => ['validate:popup'], 'uses' => 'PushController@fetchPopup']);
        //引导页 根据像素大小返回相应大小的图片
        $router->get('guide', ['uses' => 'PushController@fetchGuidePage']);
        //连登弹窗
        $router->get('unlock', ['middleware' => ['auth'], 'uses' => 'PushController@fetchUnlockLoginPopup']);
        //连登规则
        $router->get('unlock/rule', ['uses' => 'PushController@fetchUnlockRulePopup']);
    });

    /**
     *  Geetes API
     */
    $router->group(['prefix' => 'geetes'], function ($router) {
        // 极验 —— 极验一次验证
        $router->get('captcha', ['middleware' => ['api_cros', 'validate:geetesCaptcha'], 'uses' => 'GeetesController@fetchCaptcha']);
        //极验 —— 极验二次验证
        $router->get('verification', ['middleware' => ['api_cros', 'validate:geetesCaptcha'], 'uses' => 'GeetesController@fetchVerification']);

    });

    /**
     *  Location API
     */
    $router->group(['prefix' => 'location'], function ($router) {
        // 定位 —— 统计用户地址
        $router->post('', ['middleware' => ['validate:location', 'auth'], 'uses' => 'LocationController@createLocation']);
        // 地域列表
        $router->get('devices', ['middleware' => [], 'uses' => 'DeviceController@fetchDevices']);
        // 地域定位统计
        $router->post('device', ['middleware' => ['validate:device'], 'uses' => 'DeviceController@updateDeviceLocation']);
        //获取用户上次定位城市信息
        $router->get('city', ['uses' => 'DeviceController@fetchCity']);
    });

    /**
     *  Data API
     */
    $router->group(['prefix' => 'data'], function ($router) {
        // 统计活跃用户
        $router->post('activeuser', ['middleware' => ['auth'], 'uses' => 'DataController@updateActiveUser']);
        //统计产品申请点击
        $router->post('apply/log', ['middleware' => ['auth', 'validate:productdetail'], 'uses' => 'DataController@createProductApplyLog']);
        //宫格产品申请统计
        $router->post('gongge', ['middleware' => ['validate:productdetail'], 'uses' => 'DataController@createProductApplyGonggeLog']);
        //post申请记录
        $router->post('posts', ['middleware' => ['validate:pos'], 'uses' => 'DataController@createPosLog']);
        //投放统计
        $router->post('idfa', ['middleware' => ['validate:idfaId'], 'uses' => 'DataController@createUserIdfa']);
        //广告[分类专题]点击统计
        $router->get('banner/credit/card/click', ['uses' => 'DataController@createDataBannerCreditCard']);
        //区域点击统计
        $router->post('region', ['middleware' => ['validate:clickSource'], 'uses' => 'DataController@createUserRegionLog']);
        //首页访问统计
        $router->post('page/view', ['uses' => 'DataController@createPageView']);
        //产品下载统计
        $router->post('apply/download', ['middleware' => ['auth', 'validate:productdetail'], 'uses' => 'DataController@createDataProductDownloadLog']);

        /**
         * 信用卡统计 API
         */
        $router->group(['prefix' => 'credit'], function ($router) {
            // 统计信用卡申请点击
            $router->get('apply/log', ['uses' => 'BankCardDataController@applyCount']);
        });

        /**
         * 手机号发送短信 api
         */
        $router->group(['prefix' => 'event'], function ($router) {
            // 手机号发送短信
            $router->post('user/message', ['middleware' => ['validate:mobile'], 'uses' => 'EventUserController@userMessage']);
        });

        /**
         * Spread API
         * 一键选贷款统计
         */
        $router->group(['prefix' => 'spread'], function ($router) {
            // 一键选贷款点击统计
            $router->get('config/click', ['uses' => 'DataController@createDataSpreadConfig']);
        });
    });

    /**
     *  Oauth API
     */
    $router->group(['prefix' => 'oauth'], function ($router) {
        //立即申请
        $router->get('application', ['middleware' => ['auth', 'validate:oauth'], 'uses' => 'OauthController@fetchLoanmoney']);
        //立即申请
        $router->get('application/switch', ['middleware' => ['auth', 'validate:switchOauth'], 'uses' => 'OauthController@fetchApplyUrlBySwitch']);
        //立即申请手机号加密
        $router->get('mobile', ['uses' => 'OauthController@fetchDecryptMobile']);
        //立即申请撞库判断
        $router->get('judge', ['middleware' => ['validate:productdetail'], 'uses' => 'OauthController@fetchProductIsAuthenEtc']);

        //一键选贷款-申请
        $router->group(['prefix' => 'spread'], function ($router) {
            //立即申请
            $router->get('application', ['uses' => 'OauthController@fetchSpreadUrl']);
        });

        //信用卡 - 申请
        $router->group(['prefix' => 'bank'], function ($router) {
            //立即申请
            $router->get('application', ['uses' => 'OauthController@fetchCreditcardUrl']);
        });

        //一键贷功能
        $router->group(['prefix' => 'oneloan'], function ($router) {
            //立即申请
            $router->get('application', ['middleware' => ['auth', 'validate:oneloanApply'], 'uses' => 'OauthController@fetchOneloanApply']);
        });

        //合作贷
        $router->group(['prefix' => 'cooperate'], function ($router) {
            //立即申请
            $router->get('application', ['middleware' => ['validate:coopeApply'], 'uses' => 'OauthController@fetchCooperateUrl']);
        });
    });

    /**
     *  Wechat API
     */
    $router->group(['prefix' => 'wechat'], function ($router) {
        //m站 分享
        $router->post('', ['middleware' => ['validate:wechat', 'api_cros'], 'uses' => 'WechatController@fetchSignPackage']);
        //event站 分享
        $router->post('event', ['middleware' => ['validate:wechat', 'api_cros'], 'uses' => 'WechatController@fetchEventWechatShare']);
        //微信小程序 applet
        $router->get('signature', ['uses' => 'WechatController@checkSignature']);
        //微信客服
        $router->get('qrcode', ['uses' => 'WechatController@fetchOneForOneWechat']);
    });

    /**
     *  Device API
     */
    $router->group(['prefix' => 'device'], function ($router) {
        // 地域列表
        $router->get('devices', ['middleware' => ['validate:deviceId'], 'uses' => 'DeviceController@fetchDevices']);
        // 地域定位统计
        $router->post('', ['middleware' => ['validate:device'], 'uses' => 'DeviceController@updateDeviceLocation']);

    });

    /**
     *  Club API
     */
    $router->group(['prefix' => 'club'], function ($router) {
        // 速贷之家用户与论坛用户绑定
        $router->get('bind', ['middleware' => ['auth'], 'uses' => 'ClubController@clubBind']);
        $router->post('bind', ['middleware' => ['auth'], 'uses' => 'ClubController@clubBind']);
    });

    /**
     *  ProductSearch API
     */
    $router->group(['prefix' => 'product/searchs'], function ($router) {
        //搜索热刺列表
        $router->get('hots', ['uses' => 'ProductSearchController@fetchHots']);
        //搜索结果列表
        $router->get('', ['middleware' => ['validate:productsearch'], 'uses' => 'ProductSearchController@fetchSearchs']);
        //搜索反馈
        $router->post('feedback', ['middleware' => ['validate:searchfeedback', 'auth'], 'uses' => 'ProductSearchController@createFeedback']);
    });

    /**
     *  Contacts API
     */
    $router->group(['prefix' => 'contacts'], function ($router) {
        //获取通讯录
        $router->post('', ['middleware' => ['auth'], 'uses' => 'ContactsController@createOrUpdateContacts']);
    });

    /**
     *  Banks API
     *  信用卡
     */
    $router->group(['prefix' => 'banks'], function ($router) {
        //定位
        $router->group(['prefix' => 'device'], function ($router) {
            //定位提示
            $router->get('prompt', ['middleware' => ['validate:deviceId'], 'uses' => 'DeviceController@checkIsPrompt']);
        });
        //信用卡
        $router->group(['prefix' => 'creditcard'], function ($router) {
            //包壳 - 信用卡
            $router->get('', ['uses' => 'CreditcardController@fetchCreditcard']);
            //搜索热词
            $router->get('hots', ['middleware' => ['validate:deviceId'], 'uses' => 'CreditcardController@fetchHots']);
            //搜索列表
            $router->get('searches', ['middleware' => ['validate:deviceId'], 'uses' => 'CreditcardController@fetchSearches']);
            //在修改之前查询显示
            $router->get('account', ['middleware' => ['auth', 'validate:creditcardAccountId'], 'uses' => 'CreditcardAccountController@fetchBeforeAccount']);
            //创建或修改信用卡
            $router->post('account', ['middleware' => ['auth', 'validate:creditcardAccount'], 'uses' => 'CreditcardAccountController@createOrUpdateAccount']);
            //修改提醒状态
            $router->post('status', ['middleware' => ['auth', 'validate:creditcardAccountId'], 'uses' => 'CreditcardAccountController@updateRepayAlertStatus']);
            //添加或修改账单
            $router->post('bill', ['middleware' => ['auth', 'validate:creditcardBill'], 'uses' => 'CreditcardAccountController@createOrUpdateBill']);
            //已还账单更多列表
            $router->get('bills', ['middleware' => ['auth', 'validate:creditcardAccountId'], 'uses' => 'CreditcardAccountController@fetchBills']);
            //修改账单状态为已还
            $router->post('billstatus', ['middleware' => ['auth', 'validate:creditcardBillId'], 'uses' => 'CreditcardAccountController@updateBillStatus']);
            //还款提醒列表
            $router->get('accountbills', ['middleware' => ['auth'], 'uses' => 'CreditcardAccountController@fetchAccountBills']);
            //信用卡筛选头部
            $router->get('title', ['uses' => 'CreditcardController@fetchSelectTitles']);
            //信用卡筛选
            $router->get('filters', ['middleware' => ['validate:deviceIdMin'], 'uses' => 'CreditcardController@fetchCreditCardSearches']);
            //特色精选列表
            $router->get('specials', ['middleware' => ['validate:deviceIdMin', 'validate:creditcardSpecialType'], 'uses' => 'CreditcardController@fetchSpecials']);
            //办卡头条
            $router->get('headlines', ['uses' => 'CreditcardController@fetchHeadlines']);
            //办卡有礼对应产品
            $router->get('gift', ['uses' => 'CreditcardController@fetchSpecialGifts']);
            //首页推荐 热门信用卡 限制两个
            $router->get('home/specials', ['uses' => 'CreditcardController@fetchHomeSpecials']);
            //取现地址
            $router->get('cash', ['uses' => 'CreditcardController@fetchCashLink']);

        });
        //图片
        $router->group(['prefix' => 'banners'], function ($router) {
            //轮播图片
            $router->get('', ['middleware' => ['validate:bankBanner'], 'uses' => 'CreditcardBannersController@fetchBankBanners']);
            //特色精选图片
            $router->get('specials', ['uses' => 'CreditcardBannersController@fetchSpecialImages']);
            //用途卡片
            $router->get('usages', ['uses' => 'CreditcardBannersController@fetchUsageImages']);

        });

        //热门银行
        $router->get('hots', ['middleware' => ['validate:deviceIdMin'], 'uses' => 'BanksController@fetchHots']);
        //可查进度银行
        $router->get('progress', ['uses' => 'BanksController@fetchProgressBanks']);
        //银行列表
        $router->get('', ['uses' => 'BanksController@fetchHasCreditcardBanks']);
        //提醒银行列表
        $router->get('usage', ['uses' => 'BanksController@fetchBankUsages']);
        //立即激活
        $router->get('active', ['uses' => 'BanksController@fetchActives']);
        //立即提额
        $router->get('quotas', ['uses' => 'BanksController@fetchQuotas']);
        //提额银行内容
        $router->get('quota', ['middleware' => ['validate:bankId'], 'uses' => 'BanksController@fetchQuotaBankInfo']);

    });

    // 用户签到
    $router->group(['prefix' => 'sign'], function ($router) {
        // 用户签到
        $router->get('', ['middleware' => ['auth'], 'uses' => 'UserSignController@sign']);
        //用户签到 1.5倍积分
        $router->post('', ['middleware' => ['auth'], 'uses' => 'UserSignController@sign']);
    });

    // 用户信息认证
    $router->group(['prefix' => 'userauthen'], function ($router) {
        // 获取用户认证信息
        $router->get('info', ['middleware' => ['auth'], 'uses' => 'UserauthenController@fetchIdcardAuthenInfo']);
    });

    // 芝麻信用授权回调接口
    $router->group(['prefix' => 'zhima'], function ($router) {
        $router->get('score', ['uses' => 'ZhimaController@getScore']);
        // 芝麻处理成功跳转路由
        $router->get('success', ['as' => 'v1.zhima.success', 'uses' => 'ZhimaController@success']);
        // 芝麻处理失败跳转路由
        $router->get('failure', ['as' => 'v1.zhima.failure', 'uses' => 'ZhimaController@failure']);
    });

    // 用户推广接口
    $router->group(['prefix' => 'spread'], function ($router) {
        // 检测是否推广过
        $router->get('check', ['middleware' => ['validate:UserSpreadCheck'], 'uses' => 'UserSpreadController@check']);
        // 推广产品路由
        $router->post('insurance', ['middleware' => ['validate:UserSpreadInsurance'], 'uses' => 'UserSpreadController@insurance']);
        // 合作机构
        $router->get('partner', ['uses' => 'UserSpreadController@partner']);
        // 结果页
        $router->get('result', ['middleware' => ['validate:UserSpreadCheck'], 'uses' => 'UserSpreadController@result']);
        $router->get('one', ['uses' => 'UserSpreadController@one']);
        $router->get('two', ['uses' => 'UserSpreadController@two']);

        //百款聚到
        $router->get('info', ['uses' => 'UserSpreadController@fetchOneloanInfo']);
    });


    /**
     * Quickloan
     * 极速贷
     */
    $router->group(['prefix' => 'quickloan'], function ($router) {
        //点击极速贷
        $router->get('', ['middleware' => ['validate:quickloan'], 'uses' => 'QuickloanController@fetchQuickloanUrl']);

    });


    /**
     * Tools
     * 工具集
     */
    $router->group(['prefix' => 'tools'], function ($router) {
        //工具集
        $router->get('', ['uses' => 'ToolsController@fetchTools']);
        //工具对接地址
        $router->get('oauth/application', ['middleware' => ['validate:toolsId'], 'uses' => 'ToolsController@fetchToolsUrl']);


    });


    /**
     * Guides
     * 引导页
     */
    $router->group(['prefix' => 'guides'], function ($router) {
        //引导页配置
        $router->get('', ['uses' => 'GuidesController@fetchGuidesConfig']);
        //拍拍贷推广
        $router->get('promotions', ['uses' => 'GuidesController@promotionsPartner']);
    });

    /**
     * MemberExclusive
     * 会员独家
     */
    $router->group(['prefix' => 'membership'], function ($router) {
        //会员产品列表&筛选
        $router->get('', ['uses' => 'MemberExclusiveController@fetchUserVipProductList']);
        //会员产品搜索热词
        $router->get('search/hot', ['uses' => 'MemberExclusiveController@userVipSearchHot']);
        //会员产品搜索结果
        $router->get('search/result', ['middleware' => ['validate:productsearch'], 'uses' => 'MemberExclusiveController@userVipSearchResult']);
    });


    /**
     *  Test API（调试）
     */
    $router->group(['prefix' => 'test'], function ($router) {
        $router->post('', ['uses' => 'TestController@test']);
        $router->get('geetes', ['uses' => 'TestController@testGeetes']);
        $router->get('cache', ['uses' => 'TestController@getCache']);
        $router->get('comment', ['uses' => 'TestController@comment']);
        $router->get('reply', ['uses' => 'TestController@addCommentReply']);
        $router->get('product', ['uses' => 'TestController@product']);
        $router->get('rate', ['uses' => 'TestController@formatRate']);
        //修改标签状态
        $router->get('tag', ['uses' => 'TestController@updateTags']);
        $router->get('match', ['uses' => 'TestController@pregMatch']);
        //修改积分
        $router->get('credit', ['uses' => 'TestController@updateCredit']);
        //修改账户
        $router->get('account', ['uses' => 'TestController@updateAccount']);
        //转化用户名
        $router->get('username', ['uses' => 'TestController@replaceUsernameSd']);
        //将已经创建头像的用户同步到状态表
        $router->get('photo/status', ['uses' => 'TestController@createUserPhotoCreditStatus']);
        //face活体验证测试
        $router->get('alive', ['uses' => 'TestController@alive']);
        //格式化秘钥
        $router->get('chunk', ['uses' => 'TestController@getChunkSplit']);
        //天创
        $router->get('tianchuang', ['uses' => 'TestController@fetchVerifyMobileInfo3']);
        //子集
        $router->get('subset', ['uses' => 'TestController@fetchSubset']);
        $router->get('quhuafenqi', ['uses' => 'TestController@fetchQuhuafenqi']);
        $router->get('yuanzidai', ['uses' => 'TestController@fetchyuanzidai']);
        $router->get('kami', ['uses' => 'TestController@fetchKami']);
        $router->get('shuixiang', ['uses' => 'TestController@fetchshuixiang']);
        $router->get('jielebao', ['uses' => 'TestController@fetchjielebao']);
        //汇聚支付测试
        $router->get('huiju', ['uses' => 'TestController@paymentByHuiJu']);
        $router->get('fangsiling', ['uses' => 'TestController@fetchfangsiling']);

    });
});

