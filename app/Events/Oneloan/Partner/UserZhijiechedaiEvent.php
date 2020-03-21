<?php
namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;

/**
 * 智借车贷事件
 * Class UserZhijiechedaiEvent
 * @package App\Events\V1
 */
class UserZhijiechedaiEvent extends AppEvent
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