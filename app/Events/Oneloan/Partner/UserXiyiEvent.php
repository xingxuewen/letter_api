<?php

namespace App\Events\Oneloan\Partner;


use App\Events\AppEvent;

/**
 * 西伊事件
 * Class UserXiyiEvent
 * @package App\Events\V1\Partner
 */
class UserXiyiEvent extends AppEvent
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