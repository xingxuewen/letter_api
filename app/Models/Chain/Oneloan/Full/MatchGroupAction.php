<?php

namespace App\Models\Chain\Oneloan\Full;

use App\Constants\SpreadConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserSpreadFactory;
use App\Strategies\SpreadStrategy;
use App\Models\Chain\Oneloan\Full\CreateUserSpreadRelAction;

/**
 * 匹配分组
 * Class MatchGroupAction
 * @package App\Models\Chain\Oneloan\Basic
 */
class MatchGroupAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '一键选贷款full匹配分组失败！', 'code' => 1003);

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
        if ($this->fetchMatchGroup($this->params)) {
            $this->setSuccessor(new CreateUserSpreadRelAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     */
    private function fetchMatchGroup($params = [])
    {
        //logInfo('full匹配params数据', ['data' => $params]);
        $minLimitMoney = SpreadConstant::SPREAD_MONEY_LIMIT_A;
        $limitMoney = SpreadConstant::SPREAD_MONEY_LIMIT_B;
        $params['group_nid'] = [];
        if (isset($params['spread_id']) && isset($params['money']) && $params['money'] <= $limitMoney && $params['money'] >= $minLimitMoney) //金额在1万到3万
        {
            //推送d组产品
            $params['group_nid'][0] = SpreadConstant::SPREAD_GROUP_D;
        }
        //匹配用户信息，筛选用户分组
        $group = SpreadConstant::SPREAD_GROUP;
        //计数
        $sign = '';
        foreach ($group as $item) {
            //用户A组信息
            $user = SpreadStrategy::getUserInfoByGroup($params);
            //后台配置A组匹配条件
            $adminMatchaNid = $item;
            $adminMatchaId = UserSpreadFactory::fetchSpreadGroupByNid($adminMatchaNid);
            //存在类型
            if ($adminMatchaId) {
                //分组下所有类型
                $adminMatchaMoldIds = UserSpreadFactory::fetchSpreadGroupMoldRelTypeById($adminMatchaId['id']);
                //所有mold唯一标识
                $adminMatchaMolds = UserSpreadFactory::fetchSpreadMoldsByIds($adminMatchaMoldIds);
                $adminMatcha = [];
                foreach ($adminMatchaMolds as $key => $val) {
                    //类型下所有匹配条件
                    $adminMatchaConIds = UserSpreadFactory::fetchSpreadMoldCondRelByIds($val['id']);
                    //分组下所有条件值
                    $adminMatchaConVals = UserSpreadFactory::fetchSpreadConValsByIds($adminMatchaConIds);
                    $adminMatcha[$val['type_nid']] = $adminMatchaConVals;
                }
                //匹配A组是否符合
                foreach ($adminMatcha as $nid => $value) {
                    if (in_array($user[$item][$nid], $adminMatcha[$nid])) {
                        $sign .= substr($item, -1, 1) . ',';
                    }
                }
            }

        }
        //完整信息匹配结果
        if ($sign) //完整信息匹配结果
        {
            //分组去重
            $explodeSign = explode(',', substr($sign, 0, strlen($sign) - 1));
            $signArr = array_unique($explodeSign);
            $signStr = implode('', $signArr);
            //用户信息符合的分组
            $matchGroup = 'group_' . $signStr;
            $params['group_nid'][1] = $matchGroup;
        }

        //分组信息
        if(isset($params['group_nid'])) //分组信息
        {
            $group = UserSpreadFactory::fetchSpreadGroupsByNids($params['group_nid']);
            //logInfo('full_group', ['data' => $params]);
        }

        //限制推送数量
        $this->params['groupInfo'] = $group ? $group : [];
        $this->params['group_nid'] = $params['group_nid'] ? $params['group_nid'] : [];

        return true;
    }


}
