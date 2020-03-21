<?php
namespace App\Services\Lists;


use App\Redis\RedisClientFactory;
use App\Services\Lists\InfoSet\Items\MemberInfo;

class Base
{
    const IS_DEBUG = 1;

    const TYPE_GOOD = 1;
    const TYPE_HOT = 2;
    const TYPE_MAIN = 3;
    const TYPE_MAIN_NEW = 4;
    const TYPE_MAIN_CLICK = 5;
    const TYPE_SERIES_ONE = 6;
    const TYPE_SERIES_TWO = 7;
    const TYPE_SERIES_THREE = 8;

    const SORT_COMMON = 1;        //综合排序
    const SORT_SUCCESS_RATE = 2;  //下款率最高
    const SORT_NEW_ONLINE = 3;    //最新产品
    const SORT_SPEED = 4;         //速度最快
    const SORT_INTEREST_RATE = 5; //利率最低
    const SORT_QUOTA = 6;         //额度最大

    const TYPES_MAP = [
        self::TYPE_GOOD => 'Good',
        self::TYPE_HOT => 'Hot',
        self::TYPE_MAIN => 'Main',
        self::TYPE_MAIN_NEW => 'MainNew',
        self::TYPE_MAIN_CLICK => 'MainClick',
        self::TYPE_SERIES_ONE => 'SeriesOne',
        self::TYPE_SERIES_TWO => 'SeriesTwo',
        self::TYPE_SERIES_THREE => 'SeriesThree',
    ];

    protected static $_cache = null;

    protected static $_userApplyProductIds = null;

    public static function redis()
    {
        if (self::$_cache === null) {
            self::$_cache = RedisClientFactory::get();
        }

        return self::$_cache;
    }
}