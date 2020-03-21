<?php

namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;

/**
 * 任信用
 * Class UserRenxinyongEvent
 * @package App\Events\V1
 */
class UserRenxinyongEvent extends AppEvent
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