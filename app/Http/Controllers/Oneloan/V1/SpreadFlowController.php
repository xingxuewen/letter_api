<?php
namespace App\Http\Controllers\Oneloan\V1;

use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Orm\UserSpreadType;
use App\Services\Core\Oneloan\OneloanService;
use App\Strategies\SpreadStrategy;
use Illuminate\Http\Request;

/**
 * 流量分发
 * Class SpreadFlowController
 * @package APP\Http\Controllers\Oneloan\V1
 */
class SpreadFlowController extends Controller
{
    /**
     * 流量分发
     * 18
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function spreadDealApi(Request $request)
    {
        $params = $request->all();
        //用户信息完成度
        $params['finish_status'] = SpreadStrategy::fetchFinishStatus($params);
        //logInfo('接收参数', ['data' => $params]);
        //推送
        $res = OneloanService::i()->to($params);
        //logInfo('flow结果', ['data' => $res]);

        if ($params['type_nid']) {
            //logInfo('更新主表状态', ['data' => $params['type_nid']]);
            // 更新spread状态
            UserSpreadFactory::updateSpreadStatus(['mobile' => $params['mobile'], 'status' => 1]);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 城市存在同步
     */
    public function synUpdateSpreadAreasRel()
    {
        try {
            //将相同的产品城市进行同步
            $typeInfo = UserSpreadFactory::fetchSpreadTypeInfo();
            foreach ($typeInfo as $type) //遍历数组
            {
                logInfo('同步开始');
                //需要同步的type_nid
                $oneloanTypeNid = 'oneloan_' . SpreadStrategy::getExplodeTypeNid($type['type_nid']);
                //获取主键id
                $typeId = UserSpreadType::where('type_nid', $oneloanTypeNid)->value('id');
                if($typeId) {
                    //根据spread_type_id 查询sd_user_spread_type_areas_rel中的信息
                    $areaInfos = UserSpreadFactory::fetchSpreadAreasInfo($type['id']);
                    if($areaInfos) //城市存在同步
                    {
                        $res = UserSpreadFactory::createOrUpdateSpreadAreasRel($typeId,$areaInfos);
                    }
                }
            }

            return RestResponseFactory::ok(RestUtils::getStdObj());

        } catch (\Exception $exception) {
            logError('synUpdateSpreadAreasRel', $exception);
        }
    }
}
