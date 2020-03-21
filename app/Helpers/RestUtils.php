<?php

namespace App\Helpers;

/**
 * @author zhaoqiying
 */
class RestUtils
{

    public static function getStdObj()
    {
        return new \stdClass();
    }

    /**
     * 获取错误代码的错误信息
     */
    public static function getErrorMessage($code)
    {
        $codeMsg = [
            '1' => '出错啦,请重试',
            '1000' => 'url请求参数不正确',
            '1001' => '错误的token值',
            '1002' => '参数错误或未产生返回值',
            '1003' => '不存在的类名',
            '1004' => '数据添加错误',
            '1005' => '暂无数据',
            '1006' => '方法调用错误',
            '1007' => 'data 格式错误',
            '1500' => '暂无数据',
            '2000' => '出错啦,请重试',
            '2101' => '创建失败',
            '2102' => '更新失败',
            '2103' => '提交数据出错',
            '2105' => '出错啦,请刷新重试',
            '2106' => '啊哦,没有产品了',

            //用户管理
            '1101' => '用户名或密码错误',
            '1102' => '用户名或密码错误',
            '1103' => 'accessToken错误',
            '1104' => '用户已注册',
            '1105' => '用户注册成功',
            '1106' => '用户名不存在',
            '1108' => '密码修改成功',
            '1109' => '密码修改失败',
            '1110' => '等待退出',
            '1111' => '错误',
            '1112' => '请输入正确的手机号',
            '1113' => '请选择身份',
            '1114' => '绑定手机号失败',
            '1115' => '手机号已注册',
            '1116' => '用户错误',
            '1117' => '不能为空',
            '1118' => '不可重复提交',
            '1119' => '密码不能为空',
            '1120' => '抱歉，用户名已被占用...',
            '1121' => '修改手机号成功',
            '1122' => '修改手机号失败',
            '1123' => '1-20个字符，支持中英文、数字和特殊符号',
            '1124' => '手机号未注册',
            '1125' => '暂不支持修改手机号,敬请谅解!',
            '1150' => '姓名，身份证号与手机号不匹配',
            '1151' => '验证失败，请稍候重试',

            //用户身份
            '1126' => '获取身份证照片错误',
            '1127' => '请使用真实身份证',
            '11128' => '抱歉，该用户未验证过身份信息',
            '11129' => '抱歉，用户信息匹配失败',
            '12000' => '验证未通过，请身份证持有者进行认证',
            '12001' => '不可以使用软件合成人脸',
            '12002' => '不可以使用屏幕翻拍人脸',
            '12003' => '不可以使用面具',
            '12004' => '身份证号码模糊，请重新扫描',
            '12005' => '身份证性别识别错误',
            '12006' => '有效期限识别错误',
            '12007' => '不支持临时身份证！',
            '12008' => '此证件已绑定其他账号，请更换身份证！',
            '12009' => '请先完成身份证正面扫描',
            '12010' => '请使用同一身份证号认证！',
            '12011' => '请扫描身份证正面！',
            '12012' => '身份证信息存在异常，请用真实身份证！',


            //用户银行卡
            '1128' => '银行不支持',
            '1129' => '卡号错误',
            '1130' => '用户未认证',
            '1131' => '银行卡类型不一致',
            '1132' => '银行卡四要素验证未通过',
            '1133' => '银行卡删除失败',
            '11130' => '默认储蓄卡设置失败',
            '11131' => '银行卡只支持更换',
            '11132' => '选择银行卡失败，请刷新重试',

            //用户VIP
            '1134' => '已经是vip会员',
            '1135' => '请选择支付银行卡',
            '1137' => '会员创建失败',

            //订单
            '1136' => '订单创建失败',
            '1138' => '订单入数据库失败',
            '1139' => '订单类型不存在',
            '1140' => '暂停服务',
            '1141' => '查询不到该订单',
            '1142' => '交易失败,请检查信息或联系客服',

            //用户信用报告
            '13000' => '请您先进行认证才能付费费查询报告呦！',
            '13001' => '芝麻信用正在处理中！',
            '13002' => '运营商正在处理中！',
            '13003' => '报告生成中……！',
            '13004' => '支付处理中……！',
            '13005' => '未生成报告订单',
            '13006' => '报告数据正在采集中……',


            //验证码相关
            '1200' => '短信验证码发送成功',
            '1201' => '在1分钟内不能重复获取验证码',
            '1202' => '请输入手机号码',
            '1203' => '短信验证码失效',
            '1204' => '短信验证码错误,请重新输入',
            '1205' => '短信验证码下发已超上限',
            //资讯相关
            '1301' => 'sign不存在',
            '1302' => '用户不存在',
            //收藏相关
            '1401' => '请重新关注',
            '1402' => '关注成功',
            '1403' => '取消关注失败',
            //精准匹配相关
            '1501' => '用户匹配错误',
            '1502' => '基础信息完整才能匹配',
            //评论
            '1600' => '添加评论失败',
            '1601' => '暂无评论数据',
            '1602' => '添加回复失败',
            '1603' => '回复不能为空',
            '1604' => '已点击过，不可重复点击',
            '1605' => '错误',
            '1607' => '含有敏感词汇，请重新填写',
            '1608' => '请重新点击',
            '1609' => '请您输入正确额度',
            //反馈
            '1700' => '数据不能为空',
            '1701' => '反馈提交失败，请稍后再试',
            //产品相关
            '1800' => '暂时没有数据',
            '1801' => '不可重复点击',
            '1802' => '申请人数已满，请改天再来~',
            '1803' => '今日申请人数已满，明天早点来哟~',


            //服务器
            '500' => '服务器',
            //免密码登录相关
            '1900' => '不存在该平台',
            '1901' => '点击借款修改失败',
            '1902' => '非法操作',
            //积分+邀请
            '2100' => '无效的邀请',
            //极光推送
            '2200' => 'RegistrationId is not exist',
            '2201' => 'Jpush failed',
            '2202' => 'No push message',

            //信用卡
            '2300' => '对不起，您最多能添加15张卡片',
            '2301' => '请选择当前定位',
            '2302' => '抱歉，该信用卡不存在',
            '2303' => '此信用卡不可以添加账单!',
            '2304' => '账单已还，不可再次修改!',
            '2305' => '抱歉，该账单不存在',
            '2306' => '当前期数不能超过总期数',
            '2307' => '全部已还，不可进行修改',
            '2308' => '最多只能添加15张，您可以尝试删除不常用的信用卡',
            '2309' => '您今天的导入次数已用完',


            //版本信息
            '3000' => '参数不合法',
            '3001' => '版本信息获取成功',
            '3002' => '版本升级信息获取失败',
            '3003' => '当前已是最新版本！',
            //编码转义
            '4000' => '无效的编码转义',
            //计算器
            '5000' => '获取数据失败，请重新加载',

            //积分兑现金
            '6000' => '积分兑现金出错啦',
            '6001' => '积分不足',
            '6002' => '对不起,积分兑换流水插入数据失败！',
            '6003' => '对不起,用户总积分减少失败！',
            '6004' => '对不起,用户账户流水插入数据失败！',
            '6005' => '对不起,账户增加现金失败！',
            //催审
            '6100' => '已为您加速审核，请保持电话畅通~',

            //赚积分错误提示
            '6200' => '已完成新人注册~',
            '6201' => '已完成首次设置图像~',
            '6202' => '已完成首次设置用户名~',

            //提现
            '7000' => '提现出错啦',
            '7001' => '余额不足',
            '7002' => '用户账户流水表插入数据失败',
            '7003' => '用户提现流水表插入数据失败！',
            '7004' => '对不起,账户增加现金失败！',

            //产品申请加积分
            '8000' => '积分产品申请出错啦！',
            '8001' => '产品配置表判断失败!',
            '8002' => '产品已经申请,不再加积分!',
            '8003' => '积分产品申请流水表插入数据失败!',
            '8004' => '积分流水表加积分失败!',
            '8005' => '用户积分表加积分失败!',
            '8006' => '否被邀请过判断失败!',
            '8007' => '邀请人账户流水表插入数据失败!',
            '8008' => '邀请人账户表更新数据失败!',

            //联系人
            '9000' => '联系人信息不能为空',

            // 极验 3.0
            '9101' => '极验二次验证uuid验证失败!',
            '9102' => '极验二次验证失败!',
            // 用户签到
            '9103' => '今天您已签到,请明天再来~',
            '9104' => '签到失败!',
            '9105' => '签到类型不存在!',

            // 论坛
            '9106' => '注册失败!',
            '9107' => '登录失败',
            // 芝麻信用相关
            '9108' => '芝麻信用授权出错!',
            '9109' => '芝麻信用参数必须存在!',
            '9110' => '更新状态失败',
            '9111' => '你没有可用的支付宝账号!',

            //错误提示
            '20000' => '没有更新的账单数据哦！',
            '20001' => '您还没有记过账哦',

            //联登错误提示语
            '21000' => '缺少必要参数！',
            '21001' => '解密失败，请验证加密数据！',

            //汇聚支付错误骂
            '99991'=>"发送失败!请检查信息稍后重试...",

        ];

        return isset($codeMsg[$code]) ? $codeMsg[$code] : '';
    }


}
