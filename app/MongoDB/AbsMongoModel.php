<?php

namespace App\MongoDB;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

/**
 * @author zhaoqiying
 */
abstract class AbsMongoModel extends Eloquent
{

    use SoftDeletes;

    protected $connection = 'mongodb';
    
    protected $dates = ['deleted_at'];

}
