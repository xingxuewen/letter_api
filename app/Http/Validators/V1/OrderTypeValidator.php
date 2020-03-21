<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/**
 *
 *
 * Class OrderTypeValidator
 * @package App\Http\Validators\V1
 */
class OrderTypeValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     *
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
        'type.required' => 'type参数必须存在',
    );
}