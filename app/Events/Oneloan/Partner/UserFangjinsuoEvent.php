<?php
namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;

/**
 * 房金所事件
 * Class UserFangjinsuoEvent
 * @package App\Events\V1
 */
class UserFangjinsuoEvent extends AppEvent
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