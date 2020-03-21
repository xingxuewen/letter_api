<?php

namespace App\Models\Chain\Apply\SpreadApply;

use App\Constants\SpreadConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Spread\SpreadService;
use App\Strategies\SpreadStrategy;

/**
 * 获取所有信息
 *
 * Class FetchWebsiteUrlAction
 * @package App\Models\Chain\Apply\SpreadApply
 */
class FetchInfoAction extends AbstractHandler
{
    private $params = array();
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     *
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->fetchInfo($this->params) == true) {
            return $this->data;
        } else {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function fetchInfo($params)
    {
        //一键选贷款
        $typeNid = SpreadConstant::SPREAD_CONFIG_TYPE_SDZJ;
        $typeId = UserSpreadFactory::fetchConfigTypeIdByNid($typeNid);
        $spread = UserSpreadFactory::fetchConfigInfoByTypeId($typeId);

        if ($spread)  //
        {
            //用户实名
            $realname = UserIdentityFactory::fetchUserRealInfo($params['userId']);
            $spread['is_realname'] = $realname ? 1 : 0;
            $spread['url'] = $params['url'];
        }

        $this->data['url'] = $params['url'];
        $this->data['spread'] = $spread;

        return true;
    }
}