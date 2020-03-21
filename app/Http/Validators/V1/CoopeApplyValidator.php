<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/**
 * 合作贷 - 立即申请
 *
 * Class CoopeApplyValidator
 * @package App\Http\Validators\V1
 */
class CoopeApplyValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'productId' => ['required', 'integer'],
        'typeId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'productId.required' => '产品ID必须存在',
        'productId.integer' => '产品ID必须是整数',
        'typeId.required' => '产品类型必须存在',
        'typeId.integer' => '产品类型必须是整数',
    );
}