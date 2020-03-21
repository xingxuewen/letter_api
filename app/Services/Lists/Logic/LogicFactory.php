<?php
namespace App\Services\Lists\Logic;

use App\Services\Lists\Base;
use App\Services\Lists\Logic\Items\Good;
use App\Services\Lists\Logic\Items\Hot;
use App\Services\Lists\Logic\Items\LoginOne;
use App\Services\Lists\Logic\Items\LoginThree;
use App\Services\Lists\Logic\Items\LoginTwo;
use App\Services\Lists\Logic\Items\Main;
use App\Services\Lists\Logic\Items\MainClick;
use App\Services\Lists\Logic\Items\MainNew;
use App\Services\Lists\Logic\Items\Series;
use App\Services\Lists\User;
use App\Services\Lists\UserList\UserListInterface;

class LogicFactory
{

    /**
     * @param $type
     * @return LogicInterface
     * @throws \Exception
     */
    public static function factory($type) : LogicInterface
    {
        $t = Base::TYPES_MAP[$type] ?? '';

        logInfo("load {$t} logic class");

        switch ($type) {
            case Base::TYPE_HOT:
                return new Hot($type);
                break;

            case Base::TYPE_MAIN:
                return new Main($type);
                break;

            case Base::TYPE_GOOD:
                return new Good($type);
                break;

            case Base::TYPE_MAIN_NEW:
                return new MainNew($type);
                break;

            case Base::TYPE_MAIN_CLICK:
                return new MainClick($type);
                break;

            case Base::TYPE_SERIES_ONE:
                return new Series($type);
                break;
        }

        throw new \Exception('logic variable type error');
    }

}