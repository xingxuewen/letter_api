<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 *
 */

class NidValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'nid' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'nid.required' => 'nid必须存在',
    );

}
