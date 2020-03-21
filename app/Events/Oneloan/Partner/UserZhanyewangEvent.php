<?php
namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;

/**
 * 展业王事件
 * Class UserZhanyewangEvent
 * @package App\Events\V1
 */
class UserZhanyewangEvent extends AppEvent
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