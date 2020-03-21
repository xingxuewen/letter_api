<?php

namespace App\Services\Core\Validator\FaceId\Megvii;

/**
 * face常量定义
 *
 * Class MegviiConfig
 * @package App\Services\Core\Validator\FaceId\Megvii
 */
class MegviiConfig
{
    //活体验证流程的选择，目前仅取以下值：meglive：动作活体  still：静默活体

    //meglive：动作活体
    const ALIVE_MEGLIVE = 'meglive';

    //still：静默活体
    const ALIVE_STILL = 'still';

}