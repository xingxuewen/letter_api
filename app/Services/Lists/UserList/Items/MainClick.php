<?php
namespace App\Services\Lists\UserList\Items;

use App\Services\Lists\Base;
use App\Services\Lists\UserList\UserListAbstract;

class MainClick extends UserListAbstract
{
    protected $_cacheKey = 'lists_userlist_main_click_';

    protected $_type = Base::TYPE_MAIN_CLICK;

}