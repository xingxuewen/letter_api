<?php
namespace App\Events\V1;

use App\Events\AppEvent;

/**
 * 添加标签规则产品流水事件
 * Class UserInsuranceEvent
 * @package App\Events\V1
 */
class DataProductTagLogEvent extends AppEvent
{
    public $data;

    /**
     *
     * DataProductTagLogEvent constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
}