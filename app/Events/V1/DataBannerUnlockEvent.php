<?php

namespace App\Events\V1;

use App\Events\AppEvent;

/**
 * 连登点击统计
 *
 * Class UserUnlockLoginEvent
 * @package App\Events\V1
 */
class DataBannerUnlockEvent extends AppEvent
{
    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
}