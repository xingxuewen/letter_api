<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 极速贷
 */

class QuickloanValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'configId' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'configId.required' => '请传配置id',
    );

}
