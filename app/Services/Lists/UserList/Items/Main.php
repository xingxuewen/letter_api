<?php
namespace App\Services\Lists\UserList\Items;

use App\Services\Lists\Base;
use App\Services\Lists\UserList\UserListAbstract;

class Main extends UserListAbstract
{
    protected $_cacheKey = 'lists_userlist_main_';

    protected $_type = Base::TYPE_MAIN;

}