<?php
namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;

/**
 * 佳佳融事件
 * Class UserJiajiarongEvent
 * @package App\Events\V1
 */
class UserJiajiarongEvent extends AppEvent
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