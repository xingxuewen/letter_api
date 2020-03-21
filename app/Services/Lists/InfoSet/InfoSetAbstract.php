<?php
namespace App\Services\Lists\InfoSet;

use App\Services\Lists\Base;
use App\Services\Lists\User;
use App\Services\Lists\UserList\UserListInterface;

abstract class InfoSetAbstract
{
    public static function deleteCache($key)
    {
        return Base::redis()->del($key);
    }
}