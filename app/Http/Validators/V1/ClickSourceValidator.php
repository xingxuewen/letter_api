<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 统计来源
 */

class ClickSourceValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'clickSource' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'clickSource.required' => '统计来源参数必填',
    );

}
