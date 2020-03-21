<?php

namespace App\Http\Controllers\V9;

use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Events\V1\DataProductEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Redis\RedisClientFactory;
use App\Services\Lists\Base;
use App\Services\Lists\User;
use App\Services\Lists\UserList\UserListFactory;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;
use DB;

/**
 * 产品模块
 * Class ProductController
 * @package App\Http\Controllers\V8
 *
 */
class ProductTestController extends Controller
{
    /**
     * 速贷大全列表、筛选
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function main(Request $request)
    {
        $input = $request->all();

        $params = [];
        $terminalType = (int) $request->input('terminalType', 0);
        $page = (int) $request->input('pageNum', 1);
        $limit = (int) $request->input('pageSize', 10);
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        try {
            $user = new User($userId, $terminalType);
            $userList = UserListFactory::factory(Base::TYPE_MAIN);
            $data = $userList->setUser($user)
                             ->setParams($params)
                             ->setPage($page, $limit)
                             ->getData();

            var_dump($data);exit;

            /*
            //处理数据
            $params['list'] = $productLists;
            $params['pageCount'] = $pageCount;
            $params['product_vip_count'] = $counts;
            $params['product_diff_count'] = intval($diffCounts);
            $params['list_sign'] = $list_sign;
            $params['bottom_des'] = ProductConstant::BOTTOM_DES;
            $params['is_vip'] = $data['userVipType'];
            */

            $res = [
                'bottom_des' => ProductConstant::BOTTOM_DES,
                'is_vip' => $user->isVip,
            ];

            return RestResponseFactory::ok($res);
        } catch (\Exception $e) {
            logError($e->getMessage(), $e->getTraceAsString());
            return RestResponseFactory::error($e->getMessage(), $e->getCode());
        }

    }

    /**
     * 首页热门推荐
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function recommends(Request $request)
    {
        $terminalType = (int) $request->input('terminalType', 0);
        $page = (int) $request->input('pageNum', 1);
        $limit = (int) $request->input('pageSize', 10);
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        try {
            $user = new User($userId, $terminalType);
            $userList = UserListFactory::factory(Base::TYPE_HOT);
            $data = $userList->setUser($user)
                             ->setPage($page, $limit)
                             ->getData();

            var_dump($data);exit;

            $res = [
                'pageCount' => 0,
                'list' => [],
            ];
            return RestResponseFactory::ok($res);
        } catch (\Exception $e) {
            logError('recommends error', $e->getTraceAsString());
            return RestResponseFactory::error($e->getMessage(), $e->getCode());
        }
    }


    public function good(Request $request)
    {
        $terminalType = (int) $request->input('terminalType', 0);
        $page = (int) $request->input('pageNum', 1);
        $limit = (int) $request->input('pageSize', 10);
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        try {
            $user = new User($userId, $terminalType);
            $userList = UserListFactory::factory(Base::TYPE_GOOD);
            $data = $userList->setUser($user)
                ->setPage($page, $limit)
                ->getData();

            var_dump($data);exit;

            $res = [
                'pageCount' => 0,
                'list' => [],
            ];
            return RestResponseFactory::ok($res);
        } catch (\Exception $e) {
            logError($e->getMessage(), $e->getTraceAsString());
            return RestResponseFactory::error($e->getMessage(), $e->getCode());
        }
    }

    public function mainNew(Request $request)
    {
        $terminalType = (int) $request->input('terminalType', 0);
        $page = (int) $request->input('pageNum', 1);
        $limit = (int) $request->input('pageSize', 10);
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        try {
            $user = new User($userId, $terminalType);
            $userList = UserListFactory::factory(Base::TYPE_MAIN_NEW);
            $data = $userList->setUser($user)
                ->setPage($page, $limit)
                ->getData();

            var_dump($data);exit;

            $res = [
                'pageCount' => 0,
                'list' => [],
            ];
            return RestResponseFactory::ok($res);
        } catch (\Exception $e) {
            logError($e->getMessage(), $e->getTraceAsString());
            return RestResponseFactory::error($e->getMessage(), $e->getCode());
        }
    }

    public function mainClick(Request $request)
    {
        $terminalType = (int) $request->input('terminalType', 0);
        $page = (int) $request->input('pageNum', 1);
        $limit = (int) $request->input('pageSize', 10);
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        try {
            $user = new User($userId, $terminalType);
            $userList = UserListFactory::factory(Base::TYPE_MAIN_CLICK);
            $data = $userList->setUser($user)
                ->setPage($page, $limit)
                ->getData();

            var_dump($data);exit;

            $res = [
                'pageCount' => 0,
                'list' => [],
            ];
            return RestResponseFactory::ok($res);
        } catch (\Exception $e) {
            logError($e->getMessage(), $e->getTraceAsString());
            return RestResponseFactory::error($e->getMessage(), $e->getCode());
        }
    }
}