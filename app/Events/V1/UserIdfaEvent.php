<?php
namespace App\Events\V1;

use App\Events\AppEvent;

/**
 * IDFA激活回调事件
 *
 * Class UserIdfaEvent
 * @package App\Events\V1
 */
class UserIdfaEvent extends AppEvent
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