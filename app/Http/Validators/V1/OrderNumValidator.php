<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/**
 *
 *
 * Class OrderTypeValidator
 * @package App\Http\Validators\V1
 */
class OrderNumValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     *
     */
    protected $rules = array(
        'orderNum' => ['required',],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'orderNum.required' => '订单号必须存在',
    );
}