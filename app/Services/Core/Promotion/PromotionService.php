<?php

namespace App\Models\Chain\Promotion;

use App\Services\AppService;

/**
 * 推广
 *
 * Class PromotionService
 * @package App\Models\Chain\Promotion
 */
class PromotionService extends AppService
{
    public static $service;

    public static function i()
    {
        if (!(self::$service instanceof static)) {
            self::$service = new static();
        }

        return self::$service;
    }


}