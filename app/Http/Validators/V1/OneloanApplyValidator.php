<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/**
 * 一键贷功能 - 立即申请
 *
 * Class OneloanApplyValidator
 * @package App\Http\Validators\Api\V1
 */
class OneloanApplyValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'id'  => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'id.required'  => '产品ID必须存在',
        'id.integer'   => '产品ID必须是整数',
    );
}