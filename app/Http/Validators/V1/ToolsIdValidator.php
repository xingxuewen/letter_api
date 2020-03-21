<?php

namespace App\Http\Validators\V1;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

/**
 * 工具验证器
 *
 * Class ToolsValidator
 * @package App\Http\Validators\V1
 */
class ToolsIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'toolsId'   => ['required','integer','min:1'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'toolsId.required'  => 'toolsId参数必须传值',
        'toolsId.integer'  => 'toolsId参数必须是整数',
        'toolsId.min'  => 'toolsId参数值不在范围内',
    );


}
