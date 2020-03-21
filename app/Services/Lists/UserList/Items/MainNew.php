<?php
namespace App\Services\Lists\UserList\Items;

use App\Services\Lists\Base;
use App\Services\Lists\UserList\UserListAbstract;

class MainNew extends UserListAbstract
{
    protected $_cacheKey = 'lists_userlist_main_new_';

    protected $_type = Base::TYPE_MAIN_NEW;

}