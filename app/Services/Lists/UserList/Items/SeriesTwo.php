<?php
namespace App\Services\Lists\UserList\Items;

use App\Services\Lists\Base;
use App\Services\Lists\UserList\UserListAbstract;

/**
 * 连登 2
 *
 * @package App\Services\Lists\UserList\Items
 */
class SeriesTwo extends UserListAbstract
{
    protected $_cacheKey = 'lists_userlist_series_two_';

    protected $_type = Base::TYPE_SERIES_TWO;

}