<?php

namespace App\Events\V1;

use App\Events\AppEvent;

/**
 * 用户连登事件
 *
 * Class UserUnlockLoginEvent
 * @package App\Events\V1
 */
class UserUnlockLoginEvent extends AppEvent
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