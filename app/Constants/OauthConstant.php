<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 立即申请
 */
class OauthConstant extends AppConstant
{
    //立即申请产品模式匹配规则
    const SETTLE_RULES = ['cpa_register'];

    //卡密对接 - 1 办卡联登   2 H5取现联登
    const KAMI_LOGIN_TYPE = 1;
    const KAMI_CASH_LOGIN_TYPE = 2;

}

