<?php
namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;

/**
 * 小小金融2事件
 * Class UserInsuranceEvent
 * @package App\Events\V1
 */
class UserXiaoxiaoEvent extends AppEvent
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