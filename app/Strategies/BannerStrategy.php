<?php

namespace App\Strategies;

use App\Constants\BannersConstant;
use App\Constants\ProductConstant;
use App\Helpers\LinkUtils;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\BannerUnlockLoginFactory;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\PlatformProductFactory;
use App\Models\Factory\PlatformProductInterFactory;
use App\Models\Factory\PlatformProductVipFactory;
use App\Models\Factory\ProductCirculateDatetimeFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ProductUnlockLoginRelFactory;
use App\Services\Core\Store\Qiniu\QiniuService;

/**
 * 广告位策略
 *
 * Class UserStrategy
 * @package App\Strategies
 */
class BannerStrategy extends AppStrategy
{

    /**
     * @param $bannerList
     * @return mixed
     * 返回banner处理之后的图片
     */
    public static function getBanners($bannerList)
    {
        foreach ($bannerList as $key => $val) {
            $bannerList[$key]['src'] = QiniuService::getInfoImgs($val['src']);
            $newsId = stristr($val['app_url'], 'zixun');
            $newsId = substr($newsId, -2);
            $bannerList[$key]['footer_img_h5_link'] = !empty($newsId) ? LinkUtils::appLink($newsId) : '';
            $dbredirect = stristr($val['app_url'], 'dbredirect');
            //兑吧对接
            if ($dbredirect) {
                $bannerList[$key]['app_url'] = urldecode($val['app_url']);
            }
        }
        return $bannerList;
    }

    /**
     * @param $cashData
     * @return array
     * 返回首页分类专题&热门贷款
     */
    public static function getCashBanners($cashData, $hotImg)
    {
        foreach ($cashData as $key => $val) {
            $cashData[$key]['src'] = QiniuService::getInfoImgs($val['src']);
            $cashData[$key]['payback_type'] = empty($val['type_nid']) ? '' : $val['type_nid'];
        }
        $cashArr['list'] = $cashData ? $cashData : [];
        $cashArr['hot_img'] = QiniuService::getImgs($hotImg);
        return $cashArr;
    }

    /**
     * 首页分类专题 & 速贷推荐 数据处理
     * @param array $params
     * @return array
     */
    public static function getSpecialsAndRecommends($params = [])
    {
        $res = [];
        foreach ($params as $key => $val) {
            $res[$key]['id'] = $val['id'];
            $res[$key]['src'] = QiniuService::getInfoImgs($val['src']);
            $res[$key]['app_link'] = $val['app_link'];
            $res[$key]['h5_link'] = $val['h5_link'];
            $res[$key]['title'] = $val['title'];
            $res[$key]['subtitle'] = $val['subtitle'];
            $res[$key]['web_switch'] = $val['web_switch'];
            $res[$key]['type_nid'] = isset($val['type_nid']) ? $val['type_nid'] : '';
            $res[$key]['special_sign'] = BannersConstant::BANNER_SPECIAL_SIGN;
        }

        return $res;
    }

    /**
     * 置顶分类专题+信用卡
     * 分类专题无数据处理
     *
     * @param array $params
     * @return mixed
     */
    public static function getSpecialTops($params = [])
    {
        //分类专题
        $list = $params['list'];

        if ($list) {
            $res = [];
            foreach ($list as $key => $val) {
                $res[$key]['id'] = $val['id'];
                $res[$key]['src'] = QiniuService::getInfoImgs($val['src']);
                $res[$key]['app_link'] = $val['app_link'];
                $res[$key]['h5_link'] = $val['h5_link'];
                $res[$key]['title'] = $val['title'];
                $res[$key]['subtitle'] = $val['subtitle'];
                $res[$key]['web_switch'] = $val['web_switch'];
                $res[$key]['type_nid'] = isset($val['type_nid']) ? $val['type_nid'] : '';
                $res[$key]['special_sign'] = BannersConstant::BANNER_SPECIAL_TOP_SIGN;
            }
        }

        $resData['list'] = isset($res) ? $res : [];

        return $resData;
    }

    /**
     * 广告连登解锁数据处理
     *
     * @param array $params
     * @return array
     */
    public static function fetchBannerUnlockLogin($params = [], $userLogin = [], $data = [])
    {
        foreach ($params as $key => $val) {
            $params[$key]['succ_unlock_img'] = QiniuService::getImgs($val['succ_unlock_img']);
            $params[$key]['fail_unlock_img'] = QiniuService::getImgs($val['fail_unlock_img']);
            $params[$key]['unlock_sign'] = 0;
            if ($data['vip_sign'] == 1) {
                //会员全部展示
                $params[$key]['unlock_sign'] = 1;
            } else {
                $loginCount = isset($userLogin['login_count']) ? $userLogin['login_count'] : 0;
                $params[$key]['unlock_sign'] = $val['unlock_day'] <= $loginCount ? 1 : 0;
            }
            //查询解锁连登产品个数
//            $productIds = ProductFactory::fetchProductUnlockLoginByLoginId($val['id']);
//            $params[$key]['product_count'] = ProductFactory::fetchProductUnlockLoginCount($productIds);
        }

        return $params ? $params : [];
    }

    /**
     * V2 广告连登解锁数据处理
     *
     * @param array $unlocks
     * @param array $userLogin
     * @param array $data
     * @return array
     */
    public static function fetchBannerUnlockLoginV2($unlocks = [], $userLogin = [], $data = [])
    {
        $unlockLoginProductNums = self::getUnlockLoginProductNums($data);

        foreach ($unlocks as $key => $val) {
            $unlocks[$key]['succ_unlock_img'] = QiniuService::getImgs($val['succ_unlock_img']);
            $unlocks[$key]['fail_unlock_img'] = QiniuService::getImgs($val['fail_unlock_img']);
            $unlocks[$key]['unlock_pro_num'] = $unlockLoginProductNums[$val['mapid']] ?? 0;
            $unlocks[$key]['unlock_sign'] = 0;

            //对副标题中的*进行替换
            if (strpos($unlocks[$key]['unlock_subtitle'], '*') !== false) {
                $unlocks[$key]['unlock_subtitle'] = str_replace('*', $unlocks[$key]['unlock_pro_num'], $unlocks[$key]['unlock_subtitle']);
            }

            if ($data['vip_sign'] == 1) {
                //会员全部展示
                $unlocks[$key]['unlock_sign'] = 1;
            } else {
                $loginCount = isset($userLogin['login_count']) ? $userLogin['login_count'] : 0;
                $unlocks[$key]['unlock_sign'] = $val['unlock_day'] <= $loginCount ? 1 : 0;
            }
        }

        return $unlocks ? $unlocks : [];
    }

    /**
     * 获取非新用户，即登录123天用户可解锁的各产品数量
     *
     * @param $data
     * @return array
     */
    public static function getUnlockLoginProductNums($data)
    {
        $unlockLoginProductIdsGroup = self::getUnlockLoginProductIdsGroup($data);
        //连登123各组产品数量
        $unlockLoginProductNums = [];
        foreach ($unlockLoginProductIdsGroup as $key => $item) {
            $unlockLoginProductNums[$key] = count($item);
        }

        return $unlockLoginProductNums;
    }

    /**
     * 获取非新用户，即登录123天用户可解锁的各产品ID
     *
     * @param $data
     * @return array
     */
    public static function getUnlockLoginProductIdsGroup($data)
    {
        //获取unlock_login_id值
        $params = [
            'select' => ['id'],
            'where' => [
                'type_id' => $data['banner_unlock_type_id'],
            ],
            'where_not_in' => [
                'nid' => [BannersConstant::BANNER_UNLOCK_LOGIN_NID_UNLOCK_NEW],
            ],
        ];
        $unlockLoginIds = BannerUnlockLoginFactory::getAll($params);
        //通过unlock_login_id获取连登123所有产品数据,处理出连登123分别可解锁产品数
        $params = [
            'where' => [
                [
                    'status', '=', 1,
                ],
            ],
            'where_in' => [
                'unlock_login_id' => $unlockLoginIds,
            ],
        ];
        $unlockLoginProductIds = ProductUnlockLoginRelFactory::getAll($params);
        $unlockLoginProductIds = array_column($unlockLoginProductIds, null, 'product_id');
        //与主产品表中在线的产品，取交集
        $params = [
            'select' => ['platform_product_id', 'terminal_type'],
//            'where' => [
//                'online_status' => 0
//            ],
            'where_in' => [
                'platform_product_id' => array_keys($unlockLoginProductIds),
                'is_delete' => [ProductConstant::PRODUCT_IS_DELETE_UNDELETE, ProductConstant::PRODUCT_IS_DELETE_UNREAL_DELETE],
            ],
        ];
        $unlockLoginPlatformProductIds = PlatformProductFactory::getAll($params);
        //根据terminalType剔除不符合条件的产品
        $unlockLoginPlatformProductIds = array_filter($unlockLoginPlatformProductIds, function ($item, $k) use ($data) {
            $itemTerminalTypes = explode(',', $item['terminal_type']);

            if (in_array(0, $itemTerminalTypes) || in_array($data['terminalType'], $itemTerminalTypes)) {
                return true;
            }

            return false;
        }, ARRAY_FILTER_USE_BOTH);
        //取交集
        $unlockLoginPlatformProductIds = array_column($unlockLoginPlatformProductIds, 'platform_product_id');
        $unlockLoginProductIds = array_filter($unlockLoginProductIds, function ($k) use ($unlockLoginPlatformProductIds) {
            return in_array($k, $unlockLoginPlatformProductIds);
        }, ARRAY_FILTER_USE_KEY);

        //根据设备deviceNum剔除不符合条件的产品
        if ($data['deviceNum']) { //有用户定位   //@todo   4
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);
            if (!empty($deviceId)) {
                //产品城市关联表中的所有产品id
                $cityProductIds = DeviceFactory::fetchCityProductIds();
                //从所有城市ProId中去掉定位地ProId,即获得需要过滤掉的城市限制的所有ProIds
                $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);
                $filterProductIds = array_diff($cityProductIds, $deviceProductIds);

                $unlockLoginProductIds = array_filter($unlockLoginProductIds, function ($k) use ($filterProductIds) {
                    return !in_array($k, $filterProductIds);
                }, ARRAY_FILTER_USE_KEY);
//                $unlockLoginProductIds = array_diff($unlockLoginProductIds, $cityProductIds);
//                $unlockLoginProductIds = array_merge($unlockLoginProductIds, $deviceProductIds);
            }
        }

        //根据渠道过滤不符合的产品
        //渠道筛选
        if (isset($data['delivery_sign']) && $data['delivery_sign'] == 1) {
            //与渠道产品求交集
            //无对应渠道产品
            //渠道产品关联中所有产品ids
            $deliveryProAllIds = ProductFactory::fetchProIdsByDeliveryId();
            //去重
            $deliveryProAllIds = array_unique($deliveryProAllIds);
            $deliveryProIds = ProductFactory::fetchProIdsByDeliveryId($data['delivery_id']);
            $deliveryEmProIds = $deliveryProIds ? array_diff($deliveryProAllIds, $deliveryProIds) : $deliveryProAllIds;
            //渠道对应产品
            $unlockLoginProductIds = array_filter($unlockLoginProductIds, function ($k) use ($deliveryEmProIds) {
                return !in_array($k, $deliveryEmProIds);
            }, ARRAY_FILTER_USE_KEY);
        }

        //按照连登unlock_login_id将产品分组
        $unlockLoginProductIdsGroup = [];
        foreach ($unlockLoginProductIds as $item) {
            $unlockLoginProductIdsGroup[$item['unlock_login_id']][] = $item;
        }

        return $unlockLoginProductIdsGroup;
    }

    /**
     * 将产品分组为 结算/内部/会员/限量is_delete=2
     * 这里不对产品是否在主产品表在线做判断,判断主产品表在线与否由上层业务逻辑决定
     *
     * @param $productIds
     * @param $userVipType
     * @return array
     */
    public static function dealProductIdsGroupByProductType($productIds, $userVipType = '')
    {
        $productIdsByType = [];
        //1.将产品先分为 未达限量/已达限量 两类
        //限量产品
        $params = [
            'select' => ['platform_product_id'],
            'where_in' => [
                'platform_product_id' => $productIds,
            ],
            'where' => [
                'is_delete' => 2,
            ],
        ];
        $productIdsIsDelete2 = PlatformProductFactory::getAll($params);
        $productIdsIsDelete2 = array_column($productIdsIsDelete2, 'platform_product_id');//限量产品放最后
        $productIds = array_diff($productIds, $productIdsIsDelete2);
        $productIdsByType['xianliang'] = $productIdsIsDelete2 ?: [];

        //2.未达限量 产品 内部/会员/结算
        //内部产品
        $params = [
            'select' => ['product_id'],
            'where_in' => [
                'product_id' => $productIds,
            ],
            'where' => [
                'status' => 1,
            ],
        ];
        $productIdsInter = PlatformProductInterFactory::getAll($params);
        $productIdsInter = array_column($productIdsInter, 'product_id');
        $productIdsByType['neibu'] = $productIdsInter ?: [];

        $productIds = array_diff($productIds, $productIdsInter);
        //会员产品
        if ($userVipType) {
            $params = [
                'select' => ['product_id'],
                'where_in' => [
                    'product_id' => $productIds,
                ],
                'where' => [
                    'vip_type_id' => $userVipType,
                    'status' => 1,
                ],
            ];
            $productIdsVip = PlatformProductVipFactory::getAll($params);
            $productIdsVip = array_column($productIdsVip, 'product_id');
            $productIdsByType['huiyuan'] = $productIdsVip;
            $productIds = array_diff($productIds, $productIdsVip);
        }

        //结算产品
        $productIdsByType['jiesuan'] = $productIds ?: [];

        return $productIdsByType;
    }

    /**
     *
     *
     * @param $productIdsByType
     * @param $redisProIds
     * @return array
     */
    public static function dealProductIdsTypeGroupByClick($productIdsByType, $redisProIds)
    {
        $retData = [];

        foreach ($productIdsByType as $key => $item) {
            $retData[$key]['unclick'] = array_diff($item, $redisProIds);
            $retData[$key]['clicked'] = array_intersect($item, $redisProIds);
        }

        return $retData;
    }

    /**
     * 对处理好的产品分组排序
     *
     * @param $dealedData
     * @return mixed
     */
    public static function fetchProIdsByPosition($dealedData)
    {
        foreach ($dealedData as $k1 => &$v1) {
            foreach ($v1 as $k2 => &$v2) {
                $v2 = ProductFactory::fetchProIdsByPosition($v2);
            }
        }
        unset($v1, $v2);

        return $dealedData;
    }

    /**
     * 判断是否是轮播时间
     *
     */
    public static function judgeIsBannerTime()
    {
        $now = date('Y-m-d H:i:s');
        $params = [
            'where' => [
                [
                    'start_time', '<=', $now,
                ],
                [
                    'end_time', '>=', $now,
                ],
            ],
        ];
        $res = ProductCirculateDatetimeFactory::getOne($params);

        return $res ? true : false;
    }

    /**
     * 连登广告标识集合
     *
     * @param string $sign
     * @return array
     */
    public static function fetchBannerUnlockLoginNids($sign = '')
    {
        switch ($sign) {
            //连登广告第一版标识
            case BannersConstant::BANNER_UNLO_LOGIN_PRO_RECOMMEND_SIGN:
                $nids = BannersConstant::BANNER_UNLOCK_LOGIN_NIDS;
                break;
            default:
                $nids = BannersConstant::BANNER_UNLOCK_LOGIN_NIDS;
        }

        return $nids;
    }
}
