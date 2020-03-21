<?php
namespace App\Services\Lists\InfoSet\Items;

use App\Constants\ProductConstant;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ProductCirculateDatetimeFactory;
use App\Models\Factory\UserFactory;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\ProductUnlockLoginRel;
use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\InfoSetAbstract;
use App\Services\Lists\SubSet\Items\LimitAndroidProduct;
use App\Services\Lists\SubSet\Items\LimitH5Product;
use App\Services\Lists\SubSet\Items\LimitIosProduct;
use App\Services\Lists\SubSet\Items\LimitProduct;

class Product extends InfoSetAbstract
{
    const PRODUCT_ID_KEY = 'platform_product_id';

    const VIP_TYPE_ID_DEFAULT = 1;       //普通会员
    const VIP_TYPE_ID_SENIOR = 2;        //高级会员
    const VIP_TYPE_ID_COMMON_ONLINE = 3; //非会员(线上)
    const VIP_TYPE_ID_COMMON = 4;        //非会员(测试环境)

    const NO_TOP_ONE = 1;                //不排在前1
    const NO_TOP_TWO = 2;                //不排在前2
    const NO_TOP_THREE = 3;              //不排在前3

    const UNLOCK_LOGIN_TYPE_NEW_USER = 9;

    const CACHE_KEY_PRODUCT_IS_DOWNLOAD = 'lists_infoset_productIsDownload_';
    const CACHE_KEY_PRODUCTS = 'lists_infoset_products';

    protected static $_products = [];


    /**
     * 获取产品信息
     *
     * @param $ids
     * @return array
     */
    public static function get($ids)
    {
        $ids = (array) $ids;
        $res = [];
        $_ids = [];

        if (!empty($ids)) {
            foreach ($ids as $id) {
                if (isset(self::$_products[$id])) {
                    $res[$id] = self::$_products[$id];
                } else {
                    $_ids[] = $id;
                }
            }
        }

        if (!empty($_ids)) {
            $product = Base::redis()->hMGet(self::CACHE_KEY_PRODUCTS, $_ids);
            $product = empty($product) ? [] : array_filter($product);

            if (!empty($product)) {
                $product = array_map(function ($val) {
                    return json_decode($val, true);
                }, $product);
                $productIds = array_keys($product);
                $noIds = array_diff($_ids, $productIds);
            } else {
                $noIds = $_ids;
            }

            if (!empty($noIds)) {
                $productInfo = ProductFactory::productIds($noIds);
                $productInfo = array_column($productInfo, null, self::PRODUCT_ID_KEY);
                if (!empty($productInfo)) {
                    Base::redis()->hMSet(self::CACHE_KEY_PRODUCTS, array_map('json_encode', $productInfo));
                    $product = arrayMerge($product, $productInfo);
                }
            }

            if (!empty($product)) {
                $res = arrayMerge($res, $product);
                self::$_products = arrayMerge(self::$_products, $product);
            }
        }

        return $res;
    }

    /**
     * 获取产品信息 保持ids顺序
     *
     * @param array $ids
     * @return array
     */
    public static function getK(array $ids)
    {
        $list = self::get($ids);
        $res = [];

        if (!empty($list)) {
            foreach ($ids as $id) {
                if (isset($list[$id])) {
                    $res[] = $list[$id];
                }
            }
        }

        return $res;
    }

    /**
     * 所有产品和地域的关系
     *
     * @return array
     */
    public static function getProductLocation()
    {
        return DeviceFactory::fetchCityAndProductIds();
    }

    /**
     * 所有产品和端的关系
     *
     * @return array
     */
    public static function getProductTerminal()
    {
        return ProductFactory::fetchProductTerminal();
    }

    /**
     * 根据地域过滤产品
     * @param $pids
     * @param $areaId
     * @return array
     */
    public static function getProductByDevice($pids,$areaId)
    {
        return DeviceFactory::fetchProductDeviceInfo($pids, $areaId);
    }

    /**
     * 根据端得到产品
     * @param $terminalType
     * @return array
     */
    public static function getProductByTerminal($terminalType, $productIds, $getNoLimit = false)
    {
        $Product = ProductFactory::fetchProductIdByTerminal();

        $data = [];
        foreach ($Product as $v) {
            if ($getNoLimit && intval($v['terminal_type']) == 0) {
                $data[] = $v['platform_product_id'];
            } else if (strpos($v['terminal_type'], strval($terminalType)) !== false) {
                $data[] = $v['platform_product_id'];
            }
        }

        return array_intersect($data,$productIds);
    }

    /**
     * 新用户产品列表
     * @return array
     */
    public static function getNewUserProductIds()
    {
        return ProductFactory::fetchProductIdByUnlockStage(1);
    }

    /**
     * 连登1天产品列表
     * @return array
     */
    public static function getLoginOneDayProductIds()
    {
        return ProductFactory::fetchProductIdByUnlockStage(2);
    }

    /**
     * 连登2天产品列表
     * @return array
     */
    public static function getLoginTwoDaysProductIds()
    {
        return ProductFactory::fetchProductIdByUnlockStage(3);
    }

    /**
     * 连登3天产品列表
     * @return array
     */
    public static function getLoginThreeDaysProductIds()
    {
        return ProductFactory::fetchProductIdByUnlockStage(4);
    }

    /**
     * 已达限量产品列表
     * @return array
     */
    public static function getLimitProductIds()
    {
        return ProductFactory::fetchLimitProductIdsByIsDelete();
    }

    /**
     * 会员产品列表
     * @return array
     */
    public static function getVipProductIds()
    {
        $typeId = PRODUCTION_ENV ? self::VIP_TYPE_ID_COMMON_ONLINE : self::VIP_TYPE_ID_COMMON;

        $products = ProductFactory::fetchVipProductIdsByVipTypeIds([self::VIP_TYPE_ID_DEFAULT, $typeId]);

        $def = $com = [];
        foreach ($products as $v) {
            if ($v['vip_type_id']==self::VIP_TYPE_ID_DEFAULT) {
                $def[] = $v['product_id'];
            } else {
                $com[] = $v['product_id'];
            }
        }

        return array_values(array_diff($def, $com));
    }

    /**
     * 非会员产品列表
     * @return array
     */
    public static function getNotVipProductIds()
    {
        $typeId = PRODUCTION_ENV ? self::VIP_TYPE_ID_COMMON_ONLINE : self::VIP_TYPE_ID_COMMON;

        $products = ProductFactory::fetchVipProductIdsByVipTypeIds([$typeId]);

        $res = [];

        foreach ($products as $v) {
            $res[] = $v['product_id'];
        }

        return $res;
    }

    /**
     * 内部产品列表
     * @return array
     */
    public static function getInnerProductIds()
    {
        return ProductFactory::fetchInnerProductIds();
    }

    /**
     * 产品在某时间是否展示
     * @param $pids
     * @param $terminalType
     * @return array
     */
    public static function getProductShowTimes($pids,$terminalType)
    {

        $terminals = ['1'=>'ios','2'=>'android','3'=>'h5'];

        if (isset($terminals[$terminalType])) {

            $terminal = $terminals[$terminalType];

            $products = ProductFactory::fetchProductShowTimes($pids,$terminal);

            if (empty($products)) {
                return [];
            } else {

                $res = [];
                $now = date('H:i:s');

                foreach ($products as $v) {
                    if ($v['online_type']==1) {
                        if ($now<$v['total_online_start'] || $now>$v['total_online_end']) {
                            $res[] = $v['product_id'];
                        }
                    } else {
                        if($now<$v[$terminal.'_online_start'] || $now>$v[$terminal.'_online_end']) {
                            $res[] = $v['product_id'];
                        }
                    }
                }

                return $res;
            }

        } else {
            return [];
        }
    }

    /**
     * 根据标签过滤产品id
     * @param $pids
     * @param $tids
     * @param $typeId
     * @return array
     */
    public static function getProductByTags($pids,$tids,$typeId)
    {
        return ProductFactory::fetchProductIdByTagId($pids,$tids,$typeId);
    }

    /**
     * 根据端过滤产品 得到到量数据
     * @param $pids
     * @param $terminalType
     * @return array
     */
    public static function checkProductIsLimited($terminalType)
    {
        $terminals = ['1'=>'ios','2'=>'android','3'=>'h5'];

        if (isset($terminals[$terminalType])) {

            $terminal = $terminals[$terminalType];

            $products = ProductFactory::fetchProductLimitInfo($terminal);

            $res = [];
            if (!empty($products)) {
                foreach ($products as $v) {
                    if ($v[$terminal.'_status']==2) {
                        $res[] = $v['product_id'];
                    }
                }
            }
            return $res;
        } else {
            return [];
        }
    }

    /**
     * 产品在渠道是否可用
     * @param $pids
     * @param $deliveryId
     * @return array
     */
    public static function checkProductIsInDelivery($pids, $deliveryId)
    {
        return ProductFactory::fetchProductDeliveryInfo($pids, $deliveryId);
    }

    /**
     * 根据typeNid得到数量和产品id
     * @param $typeNid
     * @return array
     */
    public static function fetchProductByTypeNid($typeNid)
    {
        $type = ProductFactory::fetchProductRecommendTypeByNid($typeNid);

        $res = [];

        if (!empty($type)) {
            $res['num'] = $type['num'];

            $porducts = ProductFactory::fetchRecommendIdsByTypeId(['typeId'=>$type['id']]);

            if (!empty($porducts)) {
                $res['pids'] = $porducts;
            }
        }

        return $res;
    }

    /**
     * 内部结算产品
     * @return array
     */
    public static function getValueProductIdsByPosition()
    {
        return ProductFactory::fetchValueProductIdsByPosition();
    }

    /**
     * 根据用户行为产品过滤产品(得到不在展示时间范围中的行为产品id)
     * @return array
     */
    public static function fetchNowBehaviorProduct($porductIds)
    {
        static $info = null;

        $res = [];
        $data = self::get($porductIds);
        foreach ($data as $k=>$v) {
            if ($v['is_behavior']==1) {
                $res[] = $k;
            }
        }

        if (empty($res)) {
            return [];
        }

        if ($info === null) {
            $info = ProductFactory::fetchBehaviorProductInfo();
        }

        $flag = 0;
        if (!empty($info)) {
            $nowtime = date('H:i:s');

            foreach ($info as $v) {
                if ($nowtime>=$v['start_time'] && $nowtime<=$v['end_time']) {
                    $flag = 1;
                    break;
                }
            }
        } else {
            $flag = 1;
        }

        if ($flag==0) {
            return $res;
        } else {
            return [];
        }
    }

    /**
     * 判断当前时间是否是产品轮播时间
     * @return int
     */
    public static function checkIfCirculateDatetime()
    {

        $data = ProductCirculateDatetimeFactory::getAll(['select'=>['start_time','end_time'],'where'=>['status'=>0,'created_date'=>date('Y-m-d')]]);

        if (!empty($data)) {

            $nowtime = date('Y-m-d H:i:s');

            foreach ($data as $v) {
                if($nowtime>=$v['start_time'] && $nowtime<=$v['end_time'] ){
                    return 1;
                }
            }

            return 0;
        } else {
            return 1;
        }
    }

    /**
     * 热门推荐不可见产品列表
     * @return array
     */
    public static function getNotRecommendProductIds()
    {
        return self::getRecommendProductIds(ProductConstant::PRODUCT_RECOMMEND_HOME_UPGRADE);
    }

    /**
     * 热门推荐第1位是否轮播、速贷大全第1位是否轮播
     * 0热门推荐、速贷大全两个都返回 1返回热门推荐 2返回速贷大全
     * @return array
     */
    public static function getIsCarousel($type=0)
    {
        $productRecommendType = ProductFactory::fetchProductRecommendTypeByNid(ProductConstant::PRODUCT_RECOMMEND_HOME_UPGRADE);

        if ($type==0) {
            $data = [
                $productRecommendType['is_recommend_circulate'],//热门推荐
                $productRecommendType['is_pro_total_circulate'] //速贷大全
            ];
            return $data;
        } elseif ($type==1) {
            return $productRecommendType['is_recommend_circulate'];//热门推荐
        } else {
            return $productRecommendType['is_pro_total_circulate'];//速贷大全
        }
    }

    /**
     * 主包可见产品
     * @return array
     */
    public static function getMainShowProductIds()
    {
        return ProductFactory::fetchProIdsByIsMainShow();
    }

    /**
     * 有位置要求产品
     * @return array
     */
    public static function getPositionProductIds()
    {

        $productPositionSortRel = ProductFactory::fetchProductPositionSortRel();

        $data = [];
        if (!empty($productPositionSortRel)) {
            foreach ($productPositionSortRel as $v) {
                $data[$v['product_id']] = $v['sort_id'];
            }
        }

        return $data;
    }

    /**
     * 热门推荐展示产品数量
     * @return array
     */
    public static function getProductRecommendShowNum()
    {
        $productRecommendType = ProductFactory::fetchProductRecommendTypeByNid(ProductConstant::PRODUCT_RECOMMEND_HOME_UPGRADE);

        return $productRecommendType['num'] ?? 0;
    }


    /**
     * 优质推荐产品
     * @return array
     */
    public static function getRecommendPatternProductIds()
    {
        $productRecommendType = ProductFactory::fetchProductRecommendTypeByNid(ProductConstant::PRODUCT_SHOW);
        if (empty($productRecommendType)) {
            return [];
        }
        return ProductFactory::fetchRecommendIdsByTypeId(['typeId'=>$productRecommendType['id']]);

    }

    /**
     * 优质推荐产品方式
     * @return int 0 后台配置产品,1 系统自动选择产品, 2 系统轮播选择产品
     */
    public static function getRecommendPattern()
    {
        return ProductFactory::fetchRecommendPattern();
    }

    /**
     * 连登解锁产品
     *
     * @param $unlockLoginId
     * @return array
     */
    public static function getUnlockLoginProducts($unlockLoginId)
    {
        return ProductFactory::fetchProductUnlockLoginByLoginId($unlockLoginId);
    }

    /**
     * 根据端获取到量产品
     *
     * @param $terminalType
     * @return array
     */
    public static function terminalLimitProducts($terminalType)
    {
        $products = [];

        switch ($terminalType) {
            case 1:
                $products = (new LimitIosProduct)->getData();
                break;

            case 2:
                $products = (new LimitAndroidProduct())->getData();
                break;

            case 3:
                $products = (new LimitH5Product())->getData();
                break;
        }

        return $products;
    }

    /**
     * 获取到量产品
     *
     * @return array
     */
    public static function limitProducts($terminalType)
    {
        //已到单日总限量
        $limit = (new LimitProduct())->getData();
        //已到单日端总限量
        $userLimit =  self::terminalLimitProducts($terminalType);
        $limit = array_unique(array_merge($limit, $userLimit));

        return $limit ?: [];
    }

    /**
     * 用户是否下载甲方产品
     *
     * @param $userId
     * @return bool
     */
    public static function productIsDownload($userId)
    {
        $key = self::CACHE_KEY_PRODUCT_IS_DOWNLOAD . $userId;
        $val = (int) Base::redis()->get($key);

        if (PRODUCTION_ENV && !empty($val)) {
            return $val;
        }

        $time = UserFactory::fetchUserFirstDownloadProductInfo($userId);

        Base::redis()->setex($key, 86400, $time);

        return $time;
    }

    /**
     * 得到相关推荐产品
     *
     * @param string $typeNid
     * @return array
     */
    public static function getRecommendProductIds($typeNid)
    {
        $productRecommendType = ProductFactory::fetchProductRecommendTypeByNid($typeNid);
        return ProductFactory::fetchRecommendIdsByTypeId(['typeId'=>$productRecommendType['id']]);
    }

    /**
     * 优质推荐不可见产品列表
     *
     * @return array
     */
    public static function getNotRecommendPatternProductIds()
    {
        return self::getRecommendProductIds(ProductConstant::PRODUCT_NO_SHOW);
    }

    /**
     * 获取正常的平台id
     *
     * @return array
     */
    public static function fetchPlatformIds()
    {
        return ProductFactory::fetchPlatformIds();
    }
}