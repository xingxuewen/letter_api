<?php
namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;
use App\Helpers\Logger\SLogger;

/**
 * 猴哥贷事件
 * Class UserHougedaiEvent
 * @package App\Events\Oneloan\Partner
 */
class UserHougedaiEvent extends AppEvent
{
    public $data;

    /**
     * UserHougedaiEvent constructor.
     *
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
}