<?php
namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;

/**
 * 春雨事件
 * Class UserChunyuEvent
 * @package App\Events\V1
 */
class UserChunyuEvent extends AppEvent
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