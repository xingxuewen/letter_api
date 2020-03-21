<?php
namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;
use App\Helpers\Logger\SLogger;

/**
 * 赠送保险事件
 * Class UserInsuranceEvent
 * @package App\Events\V1
 */
class UserInsuranceEvent extends AppEvent
{
    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data = [])
    {
        //logInfo('黑牛event',['data'=>$data]);
        $this->data = $data;
    }
}