<?php

namespace App\Models\Chain\Oneloan\Full;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserSpreadFactory;
use App\Strategies\SpreadStrategy;
use App\Services\Core\Oneloan\OneloanService;

/**
 * 5. 推送产品
 * Class SpreadAction
 * @package App\Models\Chain\Oneloan\Basic
 */
class SpreadAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '一键选贷款full推送产品失败！', 'code' => 1003);

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
        if ($params['groupInfo']) //分组
        {
            foreach ($params['groupInfo'] as $key => $gval) //分组推送
            {
                $params['group_type_nid'] = $gval['type_nid'];
                //查询分组产品
                $groupProduct = UserSpreadFactory::fetchSpreadGroupRelTypeById($gval['id']);
                //获取产品唯一标识
                $spreadNids = UserSpreadFactory::fetchTypeNidsByIds($groupProduct);
                if ($spreadNids) //产品唯一标识集合
                {
                    foreach ($spreadNids as $typeNid) //遍历
                    {
                        $params['group_id'] = $gval['id'];
                        $params['type_id'] = $typeNid['id'];
                        $params['type_nid'] = $typeNid['type_nid'];
                        $params['spread_nid'] = SpreadStrategy::getSpreadNid($typeNid['type_nid']);

                        // 插入分组流水表
                        UserSpreadFactory::insertOrUpdateUserSpreadGroupLog($params);
                        $endTime = date('Y-m-d H:i:s', time());
                        // 限额类型为普通推送限额时
                        if ($gval['quota_type'] == 0)
                        {
                            $interval = $gval['interval'];
                            // 查询间隔时间内分发量
                            $count = UserSpreadFactory::fetchCountByUserId($params, $endTime, $interval);
                            // 小于限制数量时分发
                            if ($count < $gval['quota']) {
                                OneloanService::i()->to($params);
                            }
                            // 限额类型为成功推送限额时
                        } elseif ($gval['quota_type'] == 1) {
                            // 直接推送 在后续延迟分发中再做限制
                            OneloanService::i()->to($params);
                        }

                    }
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
