<?php

namespace App\Models\Chain\Oneloan\Basic;

use App\Constants\SpreadConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\OneloanService;

/**
 * 5. 推送产品
 * Class SpreadAction
 * @package App\Models\Chain\Oneloan\Basic
 */
class SpreadAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '一键选贷款basic推送产品失败！', 'code' => 1003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     *
     * @return array
     */
    public function handleRequest()
    {
        if ($this->fetchSpread($this->params)) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     *
     */
    private function fetchSpread($params = [])
    {
        if ($params['spreadNids']) //产品唯一标识集合
        {
            $spreadNids = $params['spreadNids'];
            foreach ($spreadNids as $typeNid) //遍历
            {
                $params['type_nid'] = $typeNid;
                $key = SpreadConstant::SPREAD_QUOTA . $params['group_type_nid'] . '_' . $params['mobile'];
                //判断符合条件的限制总数
                $redisQuota = CacheFactory::getValueFromCache($key);
                if (empty($redisQuota)) //限时存储
                {
                    CacheFactory::putValueToCacheToOneloan($key, 0);
                }
//                        $redisQuota = 0; $group['quota']
                if ($redisQuota < 4 ) //推送产品
                {
                    OneloanService::i()->to($params);
                    if ($params['type_nid']) //更新完成状态
                    {
                        // 更新spread状态
                        UserSpreadFactory::updateSpreadStatus(['mobile' => $params['mobile'], 'status' => 1]);
                    }

                }
            }
        }

        return true;
    }

}
