<?php
namespace App\Services\Lists\UserList\Items;

use App\Services\Lists\Base;
use App\Services\Lists\UserList\UserListAbstract;

/**
 * 连登 1
 *
 * @package App\Services\Lists\UserList\Items
 */
class SeriesOne extends UserListAbstract
{
    protected $_cacheKey = 'lists_userlist_series_one_';

    protected $_type = Base::TYPE_SERIES_ONE;

}