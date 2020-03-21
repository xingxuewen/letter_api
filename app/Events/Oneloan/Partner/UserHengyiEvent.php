<?php

namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;

/**
 * 恒昌线下恒易贷对接
 * Class UserInsuranceEvent
 * @package App\Events\V1
 */
class UserHengyiEvent extends AppEvent
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