<?php
namespace App\Constants;

/**
 *
 */
class LieXiongConstant extends AppConstant
{
    // user_vip_sub 表id
    const VIP_SUB_TYPE_MONTH = 5;
    const VIP_SUB_TYPE_SEASON = 6;
    const VIP_SUB_TYPE_YEAR = 7;

    // user_order_type 表id
    const ORDER_TYPE_MONTH = 5;
    const ORDER_TYPE_QUARTER = 4;
    const ORDER_TYPE_YEAR = 3;

    const PAYMENT_TYPE = 4;

    const CARD_MONTH = 'MONTH';
    const CARD_QUARTER = 'QUARTER';
    const CARD_YEAR = 'YEAR';

    const VIP_TYPE = [
        self::CARD_MONTH => self::VIP_SUB_TYPE_MONTH,
        self::CARD_QUARTER => self::VIP_SUB_TYPE_SEASON,
        self::CARD_YEAR => self::VIP_SUB_TYPE_YEAR,
    ];

    const ORDER_TYPE = [
        self::CARD_MONTH => self::ORDER_TYPE_MONTH,
        self::CARD_QUARTER => self::ORDER_TYPE_QUARTER,
        self::CARD_YEAR => self::ORDER_TYPE_YEAR,
    ];
}

