<?php
namespace App\Http\Validators\V3;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 获取手机验证码
 */

class CodeValidator extends AbstractValidator
{
	
	/**
	 * Validation rules
	 *
	 * @var Array
	 */
	protected $rules = array(
		'mobile' => ['required', 'regex:/^1[3|4|5|6|7|8|9]\d{9}$/'],
	);
	
	/**
	 * Validation messages
	 *
	 * @var Array
	 */
	protected $messages = array(
		'mobile.required' => '手机号必填!',
		'mobile.regex' => '手机号格式不正确，请重新输入',
	);
	
}
