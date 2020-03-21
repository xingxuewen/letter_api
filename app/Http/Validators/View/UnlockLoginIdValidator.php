<?php
namespace App\Http\Validators\View;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 解锁连登弹窗
 */

class UnlockLoginIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'unlockLoginId' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'unlockLoginId.required' => '参数必须存在',
    );

}
