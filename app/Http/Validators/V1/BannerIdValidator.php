<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 广告id验证
 */

class BannerIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'bannerId' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'bannerId.required' => '广告参数必填',
    );

}
