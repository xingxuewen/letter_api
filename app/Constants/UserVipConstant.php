<?php
/**
 * Created by PhpStorm.
 * User: zq
 * Date: 2017/10/28
 * Time: 14:38
 */


namespace App\Constants;

/**
 * VIP用户中使用的常量
 */
class UserVipConstant extends AppConstant
{
    //终端识别类型
    const YIBAO_TERMINAL_TYPE = 0;

    //vip编号长度
    const PAYMENT_PRODUCT_NUMBER_LENGTH = 32;

    //客服电话
    const CONSUMER_HOTLINE = '4000390718';

    //会员类型
    const VIP_TYPE_NID = 'vip_default';
    //会员子类型
    //年度会员
    const VIP_ANNUAL_MEMBER = 'vip_annual_member';
    //季度会员
    const VIP_QUARTERLY_MEMBER = 'vip_quarterly_member';
    //月度会员
    const VIP_MONTHLY_MEMBER = 'vip_monthly_member';

    //支付类型
    const PAYMENT_TYPE = 'YBZF';
    //汇聚支付唯一标识
    const PAYMENT_TYPE_HUIJU = 'HJZF';

    //订单类型 默认年度会员
    const ORDER_TYPE = 'user_vip';
    //年度会员
    const ORDER_VIP_ANNUAL_MEMBER = 'order_annual_member';
    //季度会员
    const ORDER_VIP_QUARTERLY_MEMBER = 'order_quarterly_member';
    //月度会员
    const ORDER_VIP_MONTHLY_MEMBER = 'order_monthly_member';

    //会员价格
    const ORDER_MEMBER_MONEY = '99';

    //会员原价
    const MEMBER_PRICE = '299';

    //商户名称
    const ORDER_DEALER_NAME = '速贷之家';

    //购买项目
    const ORDER_PRODUCT_NAME = '会员充值';

    // 购买项目 By xuyj
    const ORDER_PRODUCT_NAME_NEW = '充值会员';

    //订单描述
    const ORDER_DESC = '【会员充值】';

    //订单有效期
    const ORDER_EXPIRED_MINUTE = 30;

    //普通用户
    const VIP_TYPE_NID_VIP_COMMON = 'vip_common';
    //普通会员
    const VIP_TYPE_NID_VIP_DEFAULT = 'vip_default';

    //下款率:普通用户
    const MEMBER_COMMON_DOWN_RATE = '38%';

    //下款率:普通会员
    const MEMBER_VIP_DOWN_RATE = '86%';

    //贷款产品：普通用户
    const MEMBER_COMMON_LOAN_PRODUCT_NID = 'vip_common_loan_product';

    //贷款产品:普通会员
    const MEMBER_VIP_LOAN_PRODUCT_NID = 'vip_default_loan_product';

    //会员加积分 1.5倍积分
    const VIP_ADD_CREDIT = 'vipCredit';


    //微信号
    const WECHAT_NUMBER = 'wechat_mumber';
    //微信二维码
    const WECHAT_QRCODE = 'wechat_qrcode';


    //会员中心 - 八大特权
    //1.0.0+
    const VIP_PRIVILEGE_DEFAULT = 'vip_privilege';
    //3.1.4+
    const VIP_PRIVILEGE_UPGRADE = 'vip_privilege_upgrade';
    //3.1.7+
    //const VIP_PRIVILEGE_THIRD_UPGRADE = 'vip_privilege_third_upgrade';

    //会员中心 - 会员动态 dynamic
    const DYNAMIC_MESSAGE = [
        '',
        '享受了会员专属1对1服务',
        '已开通会员',
    ];
    //会员中心 - 会员动态 用户总数
    const DYNAMIC_USER_COUNT = 20;

    //会员中心 - 多40+贷款产品
    const VIP_PRODUCT_DIFF_COUNT = 'vip_product_loan';

}

