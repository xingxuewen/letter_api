<?php

namespace App\Http\Validators\V2;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 支付确认信息页面
 */

class PaymentInfoValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'type' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'type.required' => '充值类型必须存在',
    );

}
