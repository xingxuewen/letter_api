<?php
namespace App\Services\Lists\UserList;


use App\Services\Lists\Base;
use App\Services\Lists\UserList\Items\Good;
use App\Services\Lists\UserList\Items\Hot;
use App\Services\Lists\UserList\Items\Main;
use App\Services\Lists\UserList\Items\MainClick;
use App\Services\Lists\UserList\Items\MainNew;
use App\Services\Lists\UserList\Items\SeriesOne;
use App\Services\Lists\UserList\Items\SeriesThree;
use App\Services\Lists\UserList\Items\SeriesTwo;

class UserListFactory
{

    public static function factory($type)
    {
        switch ($type) {
            case Base::TYPE_HOT:
                return new Hot();

            case Base::TYPE_MAIN:
                return new Main();

            case Base::TYPE_MAIN_NEW:
                return new MainNew();

            case Base::TYPE_MAIN_CLICK:
                return new MainClick();

            case Base::TYPE_GOOD:
                return new Good();

            case Base::TYPE_SERIES_ONE:
                return new SeriesOne();

            case Base::TYPE_SERIES_TWO:
                return new SeriesTwo();

            case Base::TYPE_SERIES_THREE:
                return new SeriesThree();
        }

        throw new \Exception('error userlist type', 21000);
    }
}