<?php

namespace App\Models\Factory;

use App\Constants\BannersConstant;
use App\Constants\ProductConstant;
use App\Constants\UserConstant;
use App\Constants\UserVipConstant;
use App\Helpers\DateUtils;
use App\Helpers\LinkUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\ApplyProcess;
use App\Models\Orm\CooperateProduct;
use App\Models\Orm\CooperateProductType;
use App\Models\Orm\CreditCardBanner;
use App\Models\Orm\DataProductAccess;
use App\Models\Orm\DataProductApplyHistory;
use App\Models\Orm\DataProductApplyLog;
use App\Models\Orm\DataProductDayOnline;
use App\Models\Orm\DataProductDetailLog;
use App\Models\Orm\DataShadowProductDetailLog;
use App\Models\Orm\FavouritePlatform;
use App\Models\Orm\Platform;
use App\Models\Orm\PlatformComment;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\PlatformProductArea;
use App\Models\Orm\PlatformProductDeliverys;
use App\Models\Orm\PlatformProductFee;
use App\Models\Orm\PlatformProductInner;
use App\Models\Orm\PlatformProductPosition;
use App\Models\Orm\PlatformProductPositionSort;
use App\Models\Orm\PlatformProductPositionSortRel;
use App\Models\Orm\PlatformProductPortRel;
use App\Models\Orm\ProductBehaviorDatetime;
use App\Models\Orm\ProductLog;
use App\Models\Orm\PlatformProductConfig;
use App\Models\Orm\PlatformProductRecommend;
use App\Models\Orm\PlatformProductRecommendType;
use App\Models\Orm\PlatformProductSettleType;
use App\Models\Orm\PlatformProductSettleTypeRel;
use App\Models\Orm\PlatformProductTagConfig;
use App\Models\Orm\PlatformProductTagType;
use App\Models\Orm\PlatformProductUserGroup;
use App\Models\Orm\PlatformProductVip;
use App\Models\Orm\ProductCirculateDatetime;
use App\Models\Orm\ProductPropertylog;
use App\Models\Orm\ProductTag;
use App\Models\Orm\ProductTagMatch;
use App\Models\Orm\ProductUnlockLoginRel;
use App\Models\Orm\QuickloanProductRecommend;
use App\Models\Orm\QuickloanProductRecommendType;
use App\Models\Orm\ShadowCount;
use App\Models\Orm\TagSeo;
use App\Models\Orm\UserProductBlack;
use App\Models\Orm\UserProductBlackTag;
use App\Models\Orm\UserProductBlackTagLog;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\BannerStrategy;
use App\Strategies\PageStrategy;
use App\Strategies\ProductStrategy;
use Illuminate\Support\Facades\DB;

class ProductFactory extends AbsModelFactory
{

    /**
     * @param $productId
     * 返回单个产品logo && name
     */
    public static function getProductLogoAndName($productId)
    {
        $productObj = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.product_logo', 'p.platform_product_name', 'pf.platform_id', 'p.loan_min', 'p.loan_max'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['pf.is_delete' => 0, 'pf.online_status' => 1])
            ->where(['p.platform_product_id' => $productId])->first();
        return $productObj ? $productObj->toArray() : [];
    }

    /**
     * @param $productId
     * @return array
     * 获取产品额度
     */
    public static function fetchLoanMoneyById($productId)
    {
        $productObj = PlatformProduct::from('sd_platform_product as p')
            ->select(['loan_min', 'loan_max'])
            ->where(['p.platform_product_id' => $productId])->first();
        return $productObj ? $productObj->toArray() : [];
    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * 获取首页诱导轮播数据
     * @return mixed
     */
    public static function fetchPromotions()
    {
        $product = PlatformProduct::where(['p.is_delete' => 0])
            ->from('sd_platform_product as p')
            ->where(['pf.is_delete' => 0, 'pf.online_status' => 1])
            ->where('p.is_roll', '>', 0)
            ->where(['p.is_vip' => 0])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->select('p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'p.loan_min', 'p.loan_max', 'p.product_logo')
            ->get()->toArray();

        return $product;
    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * 新上线产品
     */
    public static function fetchNewOnlines()
    {
        $productLists = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.is_vip' => 0])
            ->select('p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'p.product_logo', 'p.product_introduct', 'p.update_date', 'p.create_date')
            ->orderBy('p.create_date', 'desc')
            ->limit(8)->get()->toArray();

        return $productLists;
    }

    /**
     * @param $data
     * @return bool
     * 返回分类产品对应id
     */
    public static function fetchSpecialId($data)
    {
        $bannerObj = CreditCardBanner::select(['id', 'product_list', 'title', 'img', 'remark', 'bg_color'])
            ->where(['id' => $data['specialId'], 'ad_status' => 0])
            ->first();
        return $bannerObj ? $bannerObj->toArray() : false;
    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * 分类专题对应产品
     * @param $productIdArr
     * @param $key
     * @return array
     */
    public static function fetchSpecialProducts($productIdArr, $key)
    {
        //查询产品
        $productLists = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.is_vip' => 0])
            ->whereIn('p.platform_product_id', $productIdArr)
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.loan_max', 'p.loan_min', 'p.success_count', 'p.avg_quota', 'p.fast_time', 'pro.value'])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->get()->toArray();
        $collection = collect($productLists);
        $keyed = $collection->keyBy('platform_product_id')->toArray();

        return $keyed ? $keyed : [];
    }

    /**
     * 第二版 分类专题对应产品
     * @param array $params
     * @return array
     */
    public static function fetchProductSpecials($params = [])
    {
        //查询产品
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $params['productIds'])
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $params['key']]);

        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['p.is_main_show' => 1]);
        });

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $params['productVipIds']);

        $productLists = $query->get()->toArray();
        $collection = collect($productLists);
        $keyed = $collection->keyBy('platform_product_id')->toArray();
        return $keyed ? $keyed : [];
    }

    /**
     * @param array $data
     * 全部产品、借现金有关产品 1还信用卡  2借现金
     */
    public static function fetchProducts($data = [])
    {
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 1;
        $productType = $data['productType'];
        $useType = $data['useType'];

        /* 分页START */
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo']);

        /* 条件 1还信用卡  2借现金 */
        if ($useType == 1) {
            //全部
            $query->where('p.to_use', '<>', 0);
        } elseif ($useType == 2) {   //借现金
            $query->where(['p.to_use' => 2]);
        } else {
            return false;
        }

        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页END */

        /* 排序 */
        $where = "";
        if ($productType == 1) {    //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience']);
            $query->orderBy('p.position_sort', 'asc')->orderBy('p.create_date', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc');
        } elseif ($productType == 3) {  //放款速度
            $query->addSelect(['p.loan_speed']);
            $query->orderBy('p.loan_speed', 'desc');
        } elseif ($productType == 4) {  //贷款利率
            $query->addSelect(['p.composite_rate']);
            $query->orderBy('p.composite_rate', 'desc');
        } elseif ($productType == 5) {  //最高额度
            $query->addSelect(['p.loan_max']);
            $query->orderBy('p.loan_max', 'desc');
        } else {
            return false;
        }

        $product = $query->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $productArr['list'] = $product;
        $productArr['pageCount'] = $countPage;
        return $productArr;
    }

    /**
     * @return array
     * 产品搜索——获取tag_id
     */
    public static function fetchTagId($loanNeedArr)
    {
        $tagConfigArr = PlatformProductTagConfig::select(['tag_id'])
            ->whereIn('id', $loanNeedArr)
            ->pluck('tag_id')->toArray();
        return $tagConfigArr ? $tagConfigArr : [];
    }

    /**
     * 信用卡有关产品 1还信用卡
     */
    public static function fetchCreditCards()
    {
        $creditcardArr = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'pf.platform_name',
                'p.product_introduct', 'p.product_logo', 'p.loan_speed', 'p.composite_rate', 'p.experience',
                'p.success_count'])
            ->where(['p.to_use' => 1])
            ->get()->toArray();

        return $creditcardArr;
    }

    /**
     * @param array $data
     * 产品详情——计算器
     */
    public static function fetchCalculators($data = [])
    {
        $productId = $data['productId'];
        //获取产品基础信息
        $productObj = PlatformProduct::where(['is_delete' => 0, 'platform_product_id' => $productId])
            ->select(['platform_id', 'interest_alg', 'min_rate', 'interest_alg', 'avg_quota', 'pay_method', 'loan_min',
                'loan_max', 'period_min', 'period_max'])
            ->first();
        if (empty($productObj)) {
            return false;
        }
        $productArr = $productObj->toArray();
        //平台
        $platformArr = Platform::where(['platform_id' => $productArr['platform_id'], 'online_status' => 1, 'is_delete' => 0])
            ->select(['platform_name'])->first();
        if (empty($platformArr)) {
            return false;
        }

        return $productArr ? $productArr : [];
    }

    /**
     * @param $param
     * 获取产品搜索标签
     */
    public static function fetchProductTagConfig($param)
    {
        $tagConfigArr = PlatformProductTagConfig::select(['id', 'value'])
            ->where(['key' => $param, 'status' => 1])
            ->get()->toArray();
        return $tagConfigArr ? $tagConfigArr : [];
    }

    /**
     * @param array $data
     * @return bool
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * 产品列表  Or  产品搜索
     */
    public static function fetchProductOrSearch($data = [], $deviceProductIds = [], $cityProductIds = [], $productIds = [], $deviceId = 0)
    {
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        $loanMoney = isset($data['loanMoney']) ? $data['loanMoney'] : 0;
        $indent = isset($data['indent']) ? intval($data['indent']) : 0;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.is_vip' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);

        //地域
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });

        //借款金额
        if (!empty($loanMoney)) {
            $query->where([['loan_min', '<=', $loanMoney], ['loan_max', '>=', $loanMoney]]);
        }

        //身份
        if (!empty($indent)) {
            $indent = ',' . $data['indent'];
            //获取身份对应的产品id
            //$productIdArr = ProductFactory::fetchProductIdFromIndent($indent);
            $query->where('user_group', 'like', '%' . $indent . '%');
        }

        //贷款类型
        //我需要
        if (!empty($loanNeed)) {
            $loanNeedArr = explode(',', $loanNeed);
            //获取对应的tag_id的标签
            //$loanNeedArr = ProductFactory::fetchTagId($loanNeedArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanNeedArr);
            $query->whereIn('platform_product_id', $productIdArr);
        }

        //我有
        if (!empty($loanHas)) {
            $loanHasArr = explode(',', $loanHas);
            //获取对应的tag_id的标签
            $loanHasArr = ProductFactory::fetchTagId($loanHasArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanHasArr);
            $query->whereIn('platform_product_id', $productIdArr);
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */


        /* 排序 */
        if ($productType == 1) {    //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience']);
            $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) {  //放款速度
            $query->addSelect(['p.loan_speed']);
            $query->orderBy('p.loan_speed', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) {  //贷款利率
            $query->addSelect(['p.composite_rate']);
            $query->orderBy('p.composite_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //最高额度
            $query->addSelect(['p.loan_max']);
            $query->orderBy('p.loan_max', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //新上线产品
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 7) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 8) { //新放款速度
            $query->addSelect(['pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } else {
            return false;
        }

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * @param $productId
     * 获取产品名称
     */
    public static function fetchProductname($productId)
    {
        $productArr = PlatformProduct::select(['platform_product_id', 'platform_product_name', 'platform_id', 'position_sort'])
            ->where(['is_delete' => 0])
            ->where(['platform_product_id' => $productId])
            ->first();
        if (empty($productArr)) {
            return false;
        }
        $platformArr = Platform::where(['platform_id' => $productArr['platform_id']])->first();
        if (!$platformArr) {
            return false;
        }
        return $productArr ? $productArr : [];
    }

    /** 获取产品
     * 与上下线无关
     * @param $productId
     * @return array|bool|\Illuminate\Database\Eloquent\Model|null|static
     */
    public static function fetchProduct($productId)
    {
        $productArr = PlatformProduct::select(['platform_product_id', 'platform_product_name', 'platform_id', 'position_sort'])
            ->where(['platform_product_id' => $productId])
            ->first();
        if (empty($productArr)) {
            return false;
        }
        $platformArr = Platform::where(['platform_id' => $productArr['platform_id']])->first();
        if (!$platformArr) {
            return false;
        }
        return $productArr ? $productArr : [];
    }

    ///////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param $indent
     * 通过身份获取对应的product_id
     */
    public static function fetchProductIdFromIndent($indent)
    {
        $productIdArr = PlatformProductUserGroup::select(['platform_product_id'])
            ->where(['user_group_id' => $indent])
            ->pluck('platform_product_id')->toArray();
        return $productIdArr ? $productIdArr : [];
    }

    /**
     * @param array $data
     * 获取tag_id 对应 product_id
     */
    public static function fetchProductIdFromTagId($data = [])
    {
        //速贷大全标签
        $tagTypeId = ProductFactory::fetchProductTagTypeIdByNid(ProductConstant::PRODUCT_TAG_TYPE_LOAN);
        $productIdArr = ProductTag::select(['platform_product_id as product_id'])
            ->whereIn('tag_id', $data)
            ->where(['status' => 1])
            ->where(['type_id' => $tagTypeId])
            ->pluck('product_id')->toArray();
        //去重
        $productIdArr = array_flip($productIdArr);
        $productIdArr = array_flip($productIdArr);
        $productIdArr = array_values($productIdArr);
        return $productIdArr ? $productIdArr : [];
    }

    /**
     * @param $product
     * @return mixed
     * 分组求所有产品标签
     */
    public static function tagsAll($product = [])
    {
        if (empty($product)) {
            return false;
        }
        $tagIdArr = ProductTag::select([
            'tag_id',
            'platform_product_id',
            DB::raw('GROUP_CONCAT(DISTINCT tag_id) as   tag_id'),
        ])
            ->where('status', '!=', 9)
            ->groupBy('platform_product_id')
            ->get()->toArray();
        if ($tagIdArr) {
            foreach ($tagIdArr as $key => $val) {
                $tag_id = explode(',', $val['tag_id']);
                $tagAllArr = TagSeo::select(['name', 'font_color', 'boder_color', 'bg_color'])->whereIn('id', $tag_id)->get()->toArray();
                //以产品id作为键值
                $tagArr[$val['platform_product_id']]['tag_name'] = $tagAllArr;
            }
            foreach ($product as $pk => $pv) {
                $product[$pk]['tag_name'] = isset($tagArr[$pv['platform_product_id']]['tag_name']) ? $tagArr[$pv['platform_product_id']]['tag_name'] : [];
            }
        }
        return $product;
    }

    /**
     * @param array $product
     * @param $productId
     * @return array|bool
     * 单个产品的标签
     */
    public static function tagsOnly($product = [], $productId)
    {
        if (empty($product)) {
            return false;
        }
        //标签
        $tagIdArr = ProductTag::select(['tag_id', 'platform_product_id'])
            ->where([['status', '<>', 9], 'platform_product_id' => $productId])
            ->pluck('tag_id')->toArray();
        $tagArr = TagSeo::select(['name', 'font_color', 'boder_color', 'bg_color'])->whereIn('id', $tagIdArr)->get()->toArray();
        if (empty($tagArr)) {
            $product['tag_name'] = [];
        }
        foreach ($tagArr as $key => $val) {
            $product['tag_name'][$key]['name'] = isset($val['name']) ? $val['name'] : [];
            $product['tag_name'][$key]['font_color'] = isset($val['font_color']) ? $val['font_color'] : [];
            $product['tag_name'][$key]['boder_color'] = isset($val['boder_color']) ? $val['boder_color'] : [];
            $product['tag_name'][$key]['bg_color'] = isset($val['bg_color']) ? $val['bg_color'] : [];
        }
        return $product;
    }

    /**
     * @param $productId
     * @return array
     * 产品对应标签id
     */
    public static function fetchProductTagsIdsOnly($productId, $typeId = 0)
    {
        $tagIds = ProductTag::select(['tag_id', 'platform_product_id'])
            ->where([['status', '<>', 9], 'platform_product_id' => $productId])
            ->where(['type_id' => $typeId])
            ->orderBy('position')
            ->pluck('tag_id')->toArray();
        return $tagIds ? $tagIds : [];
    }

    /**
     * @param $tagIds
     * @return array
     * 产品对应标签的名称
     */
    public static function fetchSeoTagsIdsOnly($tagIds)
    {
        $condition = implode(',', $tagIds);
        $query = TagSeo::select(['id', 'name', 'font_color', 'boder_color', 'bg_color'])
            ->whereIn('id', $tagIds);
        //排序条件
        $query->when($condition, function ($query) use ($condition) {
            $query->orderByRaw(DB::raw("FIELD(`id`, " . $condition . ')'));
        });
        $tags = $query->get()->toArray();
        return $tags ? $tags : [];
    }

    /**
     * @param array $product
     * @param $productId
     * @return array|bool
     * 按id排序 产品标签
     */
    public static function tagsByOne($product = [], $productId)
    {
        if (empty($product)) {
            return false;
        }
        //速贷大全对应id
        $typeId = self::fetchProductTagTypeIdByNid(ProductConstant::PRODUCT_TAG_TYPE_LOAN);
        $tagIds = self::fetchProductTagsIdsOnly($productId, $typeId);
        $tags = self::fetchSeoTagsIdsOnly($tagIds);
        foreach ($tags as $key => $val) {
            $product['tag_name'][$key]['name'] = isset($val['name']) ? $val['name'] : [];
            $product['tag_name'][$key]['font_color'] = isset($val['font_color']) ? $val['font_color'] : [];
            $product['tag_name'][$key]['boder_color'] = isset($val['boder_color']) ? $val['boder_color'] : [];
            $product['tag_name'][$key]['bg_color'] = isset($val['bg_color']) ? $val['bg_color'] : [];
        }
        return $product;
    }

    /**
     * @return array
     * 分组获取产品标签与position
     */
    public static function fetchProductTagsIdsAll($typeNid = '')
    {
        $tagIdsAndPositions = ProductTag::select([
            'tag_id',
            'platform_product_id',
            DB::raw('GROUP_CONCAT(is_tag) as is_tag'),
            DB::raw('GROUP_CONCAT(tag_id) as tag_id'),
            DB::raw('GROUP_CONCAT(position) as position'),
        ])
            ->where('status', '!=', 9)
            ->where(['type_id' => $typeNid])
            ->groupBy('platform_product_id')
            ->get()->toArray();
        //dd($tagIdsAndPositions);
        return $tagIdsAndPositions ? $tagIdsAndPositions : [];
    }

    /**
     * 第二版 产品标签 获取position最小的标签id
     * @return array
     */
    public static function fetchProductTag($typeId = 0)
    {
        $tagIdsAndPositions = ProductTag::select([
            'platform_product_id',
            DB::raw('GROUP_CONCAT(is_tag) as is_tag'),
            DB::raw('GROUP_CONCAT(tag_id) as tag_id'),
            DB::raw('GROUP_CONCAT(position) as position'),
        ])
            ->where('status', '!=', 9)
            ->where(['type_id' => $typeId])
            ->groupBy('platform_product_id')
            ->get()->toArray();
        //dd($tagIdsAndPositions);
        return $tagIdsAndPositions ? $tagIdsAndPositions : [];
    }

    /**
     * @param array $product
     * @return array|bool
     * 按id排序  获取产品列表的标签
     */
    public static function tagsByAll($product = [])
    {
        if (empty($product)) {
            return false;
        }
        //速贷大全对应id
        $typeId = self::fetchProductTagTypeIdByNid(ProductConstant::PRODUCT_TAG_TYPE_LOAN);
        $tagIdsAndPositions = self::fetchProductTagsIdsAll($typeId);
        $tagIds = ProductStrategy::fetchTagsIdsByPostion($tagIdsAndPositions);
        //dd($tagIds);
        if ($tagIds) {
            foreach ($tagIds as $key => $val) {
                $tag_id = explode(',', $val['tag_id']);
                $condition = $val['tag_id'];
                $query = TagSeo::select(['name', 'font_color', 'boder_color', 'bg_color'])
                    ->whereIn('id', $tag_id);
                //排序条件
                $query->when($val['tag_id'], function ($query) use ($condition) {
                    $query->orderByRaw(DB::raw("FIELD(`id`, " . $condition . ')'));
                });

                $tagAllArr = $query->get()->toArray();
                //以产品id作为键值
                $tagArr[$val['platform_product_id']]['tag_name'] = $tagAllArr;
            }
        }
        foreach ($product as $pk => $pv) {
            $product[$pk]['tag_name'] = isset($tagArr[$pv['platform_product_id']]['tag_name']) ? $tagArr[$pv['platform_product_id']]['tag_name'] : [];
        }
        return $product;
    }

    /**
     * 产品列表标签  获取第一个标签作为产品标签
     * @param array $product
     * @return array|bool
     */
    public static function tagsLimitOneToProducts($product = [])
    {
        //速贷大全标签
        $typeId = self::fetchProductTagTypeIdByNid(ProductConstant::PRODUCT_TAG_TYPE_LOAN);
        $tagIds = self::fetchProductTag($typeId);
        $tagIds = ProductStrategy::fetchTagsIdsByPostion($tagIds);

        //根据标签id查找标识名称
        if ($tagIds) {
            $tag_ids = $tagAll = [];
            foreach ($tagIds as $key => $val) {
                if (empty($val['tag_id'])) {
                    continue;
                }
                $tag_id = explode(',', $val['tag_id']);
                $tag_ids[] = $tag_id[0];
            }

            if (!empty($tag_ids)) {
                $tagAll = TagSeo::select(['id', 'name'])->whereIn('id', $tag_ids)->get()->toArray();
                $tagAll = empty($tagAll) ? [] : array_column($tagAll, 'name', 'id');
            }

            foreach ($tagIds as $key => $val) {
                $tag_id = explode(',', $val['tag_id']);
                $is_tag = explode(',', $val['is_tag']);
                //以产品id作为键值
                $tagArr[$val['platform_product_id']]['tag_name'] = $tagAll[$tag_id[0]] ?? '';
                $tagArr[$val['platform_product_id']]['is_tag'] = $is_tag ? $is_tag[0] : 0;
            }

            /*  old
            foreach ($tagIds as $key => $val) {
                $tag_id = explode(',', $val['tag_id']);
                $is_tag = explode(',', $val['is_tag']);
                $query = TagSeo::select(['name'])
                    ->where(['id' => $tag_id[0]])->first();
                $tag = $query ? $query->toArray() : [];
                //以产品id作为键值
                $tagArr[$val['platform_product_id']]['tag_name'] = $tag ? $tag['name'] : '';
                $tagArr[$val['platform_product_id']]['is_tag'] = $is_tag ? $is_tag[0] : 0;
            }
            */
        }

        foreach ($product as $pk => $pv) {
            $product[$pk]['tag_name'] = isset($tagArr[$pv['platform_product_id']]['tag_name']) ? $tagArr[$pv['platform_product_id']]['tag_name'] : '';
            $product[$pk]['is_tag'] = isset($tagArr[$pv['platform_product_id']]['is_tag']) ? intval($tagArr[$pv['platform_product_id']]['is_tag']) : 0;
        }

        return $product;
    }


    /**
     * 单个产品详情
     * @param $productId
     * @return bool
     */
    public static function productOne($productId)
    {
        $query = PlatformProduct::where(['is_delete' => 0, 'platform_product_id' => $productId]);

        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['is_main_show' => 1]);
        });

        $productObj = $query->first();
        if (empty($productObj)) {
            return false;
        }
        $productArr = $productObj->toArray();
        $platformObj = Platform::where(['platform_id' => $productArr['platform_id'], 'online_status' => 1, 'is_delete' => 0])
            ->select(['platform_name'])->first();
        if (empty($platformObj)) {
            return false;
        }
        $productArr['platform_name'] = $platformObj->platform_name;
        return $productArr;
    }

    /**
     * 单个产品详情
     * 与产品上下线无关
     * 与平台上下线无关
     *
     * @param $productId
     * @return bool
     */
    public static function productOneFromProNothing($productId)
    {
        $productObj = PlatformProduct::where(['platform_product_id' => $productId])
            ->first();
        if (empty($productObj)) {
            return false;
        }
        $productArr = $productObj->toArray();
        $platformObj = Platform::where(['platform_id' => $productArr['platform_id']])
            ->select(['platform_name'])->first();
        if (empty($platformObj)) {
            return false;
        }
        $productArr['platform_name'] = $platformObj->platform_name;
        return $productArr;
    }

    /**
     * @param $id
     * @return array
     * @desc    申请流程
     * api4
     */
    public static function applicationProcess($id)
    {
        $ids = explode(',', $id);
        $process = [];
        foreach ($ids as $key => $v) {
            $val = ApplyProcess::where(['id' => $v])->first();
            if ($val) {
                $val = $val->toArray();
                $process[$key]['id'] = isset($val['id']) ? $val['id'] : 0;
                $process[$key]['name'] = isset($val['name']) ? $val['name'] : '';
                $process[$key]['img'] = isset($val['img']) ? QiniuService::getImgs($val['img']) : '';
            }
        }
        return $process;
    }

    /**
     * 统计通过率
     * api4
     */
    public static function passRate($productId)
    {
        $pid = $productId;
        // 0=>1 1=>2 2=>3 3=>4 4=>5
        //通过率 = 评论结果值  1+5 /(1+2+5)  1，拿到钱 2，悲剧咧 3 其他 4 申请中 ， 5 有额度，
        $one = PlatformComment::where(['platform_product_id' => $pid, 'result' => 1])->count();
        $two = PlatformComment::where(['platform_product_id' => $pid, 'result' => 2])->count();
        $five = PlatformComment::where(['platform_product_id' => $pid, 'result' => 5])->count();

        if (($one + $two + $five) > 0) {
            $oneTwo = bcadd($one, $five);
            $oneFive = bcadd($one, bcadd($two, $five));
            $pass_rate = bcdiv($oneTwo, $oneFive, 2);
        } else {
            $pass_rate = 0;
        }

        return $pass_rate;
    }

    /**
     * @param $creditProId
     * 返回下限的产品id
     */
    public static function updateProductApply($creditProId)
    {
        $productIdArr = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $creditProId)
            ->select('p.platform_product_id')
            ->pluck('p.platform_product_id')
            ->toArray();
        $productId = array_diff($creditProId, $productIdArr);
        return $productId;
    }

    /**
     * @param $userId
     * @param $productId
     * @return array
     * 通过user_id && product_id 获取收藏信息
     */
    public static function fetchCollectionByUidAndPid($userId, $productId)
    {
        $collectionObj = FavouritePlatform::select('platform_product_id')
            ->where(['user_id' => $userId])
            ->where(['platform_product_id' => $productId])
            ->first();

        return $collectionObj ? $collectionObj->toArray() : [];
    }

    /**
     * @param $productId
     * 增加产品点击量
     */
    public static function updateProductClick($productId)
    {
        $product = PlatformProduct::select()->where(['platform_product_id' => $productId])->first();
        $product->increment('click_count', 1);
        return $product->save();
    }

    /**
     * @return array
     * 所有产品id
     */
    public static function fetchProductIds()
    {
        $productIds = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.product_logo', 'p.platform_product_name', 'pf.platform_id'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['pf.is_delete' => 0, 'pf.online_status' => 1])
            ->pluck('p.platform_product_id')->toArray();

        return $productIds ? $productIds : [];
    }

    /**
     * 首页推荐产品  显示32条产品  默认排序
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * @param array $data
     * @return mixed
     */
    public static function fetchRecommends($data = [])
    {
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.success_rate', 'p.product_logo', 'p.loan_min', 'p.loan_max', 'p.terminal_type'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.is_vip' => 0])
            ->orderBy('p.position_sort', 'asc')
            ->orderBy('p.create_date', 'desc')
            ->orderBy('p.update_date', 'desc');

        //根据终端类型筛选产品
        $terminalType = $data['terminalType'];
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        $count = $query->count();
        $page = PageStrategy::getPage($count, $data['pageSize'], $data['pageNum']);
        $productArr = $query
            ->limit($page['limit'])
            ->offset($page['offset'])
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $page['pageCount'] ? $page['pageCount'] : 0;

        return $product;
    }

    /**
     * @param $platformId
     * @param string $productId
     * @return array
     * 计算器
     */
    public static function fetchCounter($productId)
    {
        //获取产品基础信息
        $products = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.platform_product_id' => $productId])
            ->select(['p.platform_id', 'p.interest_alg', 'p.min_rate', 'p.interest_alg', 'p.avg_quota', 'p.pay_method', 'p.loan_min', 'p.loan_max', 'p.period_min', 'p.period_max'])
            ->first();

        return $products ? $products->toArray() : [];
    }

    /**
     * @param $userId
     * @param array $data
     * @param array $user
     * @param array $products
     * @param array $deliverys
     * @return bool
     * 查看产品详情流水
     */
    public static function createProductLog($userId, $data = [], $user = [], $products = [], $deliverys = [])
    {
        $productLog = new DataProductDetailLog();
        $productLog->user_id = $userId;
        $productLog->username = $user['username'];
        $productLog->mobile = $user['mobile'];
        $productLog->platform_id = $products['platform_id'];
        $productLog->platform_product_id = $products['platform_product_id'];
        $productLog->platform_product_name = $products['platform_product_name'];
        $productLog->product_is_vip = isset($data['is_vip_product']) ? $data['is_vip_product'] : 99;
        $productLog->position = $products['position_sort'];
        $productLog->device_id = isset($data['deviceId']) ? $data['deviceId'] : '';
        $productLog->click_source = isset($data['clickSource']) ? $data['clickSource'] : '';
        $productLog->user_agent = UserAgent::i()->getUserAgent();
        $productLog->channel_id = isset($deliverys['id']) ? $deliverys['id'] : '';
        $productLog->channel_title = isset($deliverys['title']) ? $deliverys['title'] : '';
        $productLog->channel_nid = isset($deliverys['nid']) ? $deliverys['nid'] : '';
        $productLog->create_at = date('Y-m-d H:i:s', time());
        $productLog->create_ip = Utils::ipAddress();
        return $productLog->save();
    }

    /**
     * 马甲产品详情点击流水统计
     * @param $userId
     * @param array $data
     * @param array $user
     * @param array $products
     * @param array $deliverys
     * @return bool
     */
    public static function createShadowProductLog($userId, $data = [], $user = [], $products = [], $deliverys = [])
    {
        $productLog = new DataShadowProductDetailLog();
        $productLog->user_id = $userId;
        $productLog->username = $user['username'];
        $productLog->mobile = $user['mobile'];
        $productLog->platform_id = $products['platform_id'];
        $productLog->platform_product_id = $products['platform_product_id'];
        $productLog->platform_product_name = $products['platform_product_name'];
        $productLog->product_is_vip = isset($data['is_vip_product']) ? $data['is_vip_product'] : '99';
        $productLog->shadow_nid = $data['shadowNid'];
        $productLog->user_agent = UserAgent::i()->getUserAgent();
        $productLog->channel_id = isset($deliverys['id']) ? $deliverys['id'] : '';
        $productLog->channel_title = isset($deliverys['title']) ? $deliverys['title'] : '';
        $productLog->channel_nid = isset($deliverys['nid']) ? $deliverys['nid'] : '';
        $productLog->position = isset($data['position']) ? $data['position'] : '99';
        $productLog->device_id = isset($data['deviceId']) ? $data['deviceId'] : '';
        $productLog->click_source = isset($data['clickSource']) ? $data['clickSource'] : '';
        $productLog->create_at = date('Y-m-d H:i:s', time());
        $productLog->create_ip = Utils::ipAddress();
        return $productLog->save();
    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * @param $data
     * @return array|bool
     * 速贷大全列表 or 速贷大全搜索列表
     */
    public static function fetchProductsOrSearchs($data)
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        $loanMoney = isset($data['loanMoney']) ? $data['loanMoney'] : 0;
        $indent = isset($data['indent']) ? intval($data['indent']) : 0;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;
        //所有产品id
        $productIds = $data['productIds'];
        //产品城市关联表中的所有产品id
        $cityProductIds = $data['cityProductIds'];
        //地域对应产品id
        $deviceProductIds = $data['deviceProductIds'];
        //地域id
        $deviceId = $data['deviceId'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0, 'p.is_vip' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);

        //地域
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });

        //根据终端类型筛选产品
        $terminalType = $data['terminalType'];
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //借款金额
        $query->when($loanMoney, function ($query) use ($loanMoney) {
            $query->where([['loan_min', '<=', $loanMoney], ['loan_max', '>=', $loanMoney]]);
        });

        //身份
        $query->when($indent, function ($query) use ($indent) {
            $indent = ',' . $indent;
            //获取身份对应的产品id
            $query->where('user_group', 'like', '%' . $indent . '%');
        });

        //贷款类型
        //我需要
        $query->when($loanNeed, function ($query) use ($loanNeed) {
            $loanNeedArr = explode(',', $loanNeed);
            //获取对应的tag_id的标签
            //$loanNeedArr = ProductFactory::fetchTagId($loanNeedArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanNeedArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        //我有
        $query->when($loanHas, function ($query) use ($loanHas) {
            $loanHasArr = explode(',', $loanHas);
            //获取对应的tag_id的标签
            //$loanHasArr = ProductFactory::fetchTagId($loanHasArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanHasArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        /* 排序 */
        if ($productType == 1) {     //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience']);
            $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->addSelect(['p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
        } else {
            return false;
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /*** 马甲包速贷大全工厂方法
     * @param array $data
     * @return array|bool
     */
    public static function fetchProductsShadow($data = [])
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;

        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'po.position_sort', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->Leftjoin('sd_platform_product_position as po', 'p.platform_product_id', '=', 'po.product_id')
            ->where(['po.online_status' => 1, 'po.shadow_id' => $data['shadowId']]);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        /* 排序 */
        if ($productType == 1) {     //综合指数
            $query->addSelect(['po.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience']);
            $query->orderBy('po.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->addSelect(['p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
        } else {
            return false;
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * @return array
     * 代还信用卡产品
     * 最多显示10个
     */
    public static function fetchGiveBackProducts()
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.fast_time', 'pro.value', 'p.interest_alg', 'p.min_rate', 'p.avg_quota'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->orderBy('p.position_sort', 'asc')
            ->orderBy('p.platform_product_id', 'desc')
            ->limit(10)->get()->toArray();

        return $query ? $query : [];
    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * @param $data
     * @return array
     * 还款提醒中的推荐产品
     */
    public static function fetchAccountAlertProducts($data)
    {
        //分页
        $pageSize = $data['pageSize'];
        $pageNum = $data['pageNum'];
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $data['productIds'])
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.fast_time', 'pro.value', 'p.interest_alg', 'p.min_rate', 'p.avg_quota'])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $data['key']])
            ->where(['p.is_vip' => 0])
            ->orderByRaw(DB::raw("FIELD(`p`.`platform_product_id`, " . $data['condition'] . ')'));
        //排序
        $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * @param $typeNid
     * @return array
     * 代还产品id
     */
    public static function fetchSpecialProductIdsByTypeNid($typeNid)
    {
        $productIds = CreditCardBanner::select(['product_list'])
            ->where(['type_nid' => $typeNid, 'ad_status' => 0])
            ->orderBy('utime', 'desc')
            ->limit(1)
            ->first();
        return $productIds ? $productIds->toArray() : [];
    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * @param $data
     * @return array
     * 代还对应分类专题产品
     */
    public static function fetchSpecialProductsByTypeNid($data)
    {
        //查询产品
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $data['productIds'])
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.fast_time', 'pro.value', 'p.interest_alg', 'p.min_rate', 'p.avg_quota'])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $data['key']])
            ->where(['p.is_vip' => 0]);

        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['p.is_main_show' => 1]);
        });

        $productLists = $query->orderByRaw(DB::raw("FIELD(`p`.`platform_product_id`, " . $data['condition'] . ')'))
            ->limit(20)
            ->get()->toArray();

        return $productLists ? $productLists : [];
    }

    /**
     * 用户产品申请记录
     * @param array $data
     * @return array
     */
    public static function fetchApplyHistorysByUserId($data = [])
    {
        $log = DataProductApplyHistory::select(['id', 'platform_product_id', 'is_urge', 'created_at', 'user_id', 'platform_id'])
            ->where(['user_id' => $data['userId']])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        return $log ? $log : [];
    }

    /**
     * @param array $params
     * @return array
     * 产品申请记录产品
     */
    public static function fetchHistoryProducts($params = [])
    {
        foreach ($params as $key => $val) {
            //放款时间
            $keys = ProductConstant::PRODUCT_LOAN_TIME;
            //查询
            $query = PlatformProduct::from('sd_platform_product as p')
                ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                    'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid', 'p.is_vip_product', 'p.service_mobile'])
                ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
                ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
                ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
                ->where(['pro.key' => $keys])
                ->where(['p.platform_product_id' => $val['platform_product_id']]);

            //速贷之家主包标识
            $ua = Utils::fetchUserAgentParam();
            $query->when($ua, function ($query) use ($ua) {
                $query->where(['p.is_main_show' => 1]);
            });

            $query = $query->first();

            $params[$key]['product'] = $query ? $query->toArray() : [];
        }

        return $params;
    }

    /**
     * @param $data
     * @return array
     * 判断是否可以进行催审
     */
    public static function fetchUrgeById($data)
    {
        $urge = DataProductApplyHistory::select(['id'])
            ->where(['is_urge' => 1, 'id' => $data['urgeId']])
            ->first();

        return $urge ? $urge->toArray() : [];
    }

    /**
     * @param $params
     * @return mixed
     * 修改催审状态为已催审
     */
    public static function updateHistoryUrge($params)
    {
        return DataProductApplyHistory::where(['id' => $params['urgeId'], 'is_urge' => 0])->update(['is_urge' => 1]);
    }

    /**
     * 产品列表 & 速贷大全筛选 数据
     * @param array $data
     * @return array|bool
     */
    public static function fetchProductsOrFilters($data = [])
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        //身份
        //$indent = isset($data['indent']) ? intval($data['indent']) : 0;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //贷款金额
        $loanAmount = empty($data['loanAmount']) ? [] : explode(',', $data['loanAmount']);
        //贷款期限
        $loanTerm = empty($data['loanTerm']) ? [] : explode(',', $data['loanTerm']);

        //所有产品id
        $productIds = $data['productIds'];
        //产品城市关联表中的所有产品id
        $cityProductIds = $data['cityProductIds'];
        //地域对应产品id
        $deviceProductIds = $data['deviceProductIds'];
        //地域id
        $deviceId = $data['deviceId'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $data['productVipIds']);

        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['p.is_main_show' => 1]);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //不想看产品筛选
        $blackIds = $data['blackIds'];
        $query->when($blackIds, function ($query) use ($blackIds) {
            $query->whereNotIn('p.platform_product_id', $blackIds);
        });

        //地域筛选
        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });


        //借款金额
        $query->when($loanAmount, function ($query) use ($loanAmount) {
            $query->where(function ($query) use ($loanAmount) {
                if (empty($loanAmount[1])) {
                    $query->where('loan_min', '>', $loanAmount[0])->orWhere('loan_max', '>', $loanAmount[0]);
                } else {
                    $query->whereBetween('loan_min', $loanAmount)->orWhereBetween('loan_max', $loanAmount);
                    $query->orWhere(function ($query) use ($loanAmount) {
                        $query->where('loan_min', '<', $loanAmount[0])->where('loan_max', '>', $loanAmount[1]);
                    });
                }
            });
        });

        //借款期限
        $query->when($loanTerm, function ($query) use ($loanTerm) {
            $query->where(function ($query) use ($loanTerm) {
                if (empty($loanTerm[1])) {
                    $query->where('period_min', '>', $loanTerm[0])->orWhere('period_max', '>', $loanTerm[0]);
                } else {
                    $query->whereBetween('period_min', $loanTerm)->orWhereBetween('period_max', $loanTerm);
                    $query->orWhere(function ($query) use ($loanTerm) {
                        $query->where('period_min', '<', $loanTerm[0])->where('period_max', '>', $loanTerm[1]);
                    });
                }
            });
        });

        //贷款类型
        //我需要
        $query->when($loanNeed, function ($query) use ($loanNeed) {
            $loanNeedArr = explode(',', $loanNeed);
            //获取对应的tag_id的标签
            //$loanNeedArr = ProductFactory::fetchTagId($loanNeedArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanNeedArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        //我有
        $query->when($loanHas, function ($query) use ($loanHas) {
            $loanHasArr = explode(',', $loanHas);
            //获取对应的tag_id的标签
            //$loanHasArr = ProductFactory::fetchTagId($loanHasArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanHasArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        if (isset($data['vip_sign']) && $data['vip_sign']) { //vip登录特殊vip在下
            $query->orderBy('is_special_vip', 'asc');
        }

        /* 排序 */
        if ($productType == 1) {     //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience', 'p.is_vip']);
            $query->orderBy('p.position_sort', 'asc')
                ->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->addSelect(['p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
        } else {
            return false;
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 同类产品推荐
     * @param array $data
     * @return array
     */
    public static function fetchLikeProducts($data = [])
    {
        //分页 默认显示5条数据
        $pageSize = isset($data['pageSize']) ? $data['pageSize'] : 1;
        $pageNum = isset($data['pageNum']) ? $data['pageNum'] : 5;

        //所有产品id
        $productIds = $data['productIds'];
        //产品城市关联表中的所有产品id
        $cityProductIds = $data['cityProductIds'];
        //地域对应产品id
        $deviceProductIds = $data['deviceProductIds'];
        //地域id
        $deviceId = $data['deviceId'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link',
                'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type',
                'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //用户可以看产品
        $query->whereIn('p.platform_product_id', $data['productVipIds']);

        //排除该详情的产品id
        $productId = isset($data['productId']) ? $data['productId'] : '';
        $query->when($productId, function ($query) use ($productId) {
            $query->where('p.platform_product_id', '!=', $productId);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //不想看产品筛选
        $blackIds = $data['blackIds'];
        $query->when($blackIds, function ($query) use ($blackIds) {
            $query->whereNotIn('p.platform_product_id', $blackIds);
        });

        //地域筛选
        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });

        if (isset($data['vip_sign']) && $data['vip_sign']) { //vip登录特殊vip在下
            $query->orderBy('is_special_vip', 'asc');
        }
        //定位排序
        $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }


    /**
     * 第四版相似产品推荐
     *
     * @param array $data
     * @return array
     */
    public static function fetchLikeProductOrSearchs($data = [])
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);

        //所有产品id
        $productIds = $data['productIds'];
        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $delProIds = $data['delProIds'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $productIds);

        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['p.is_main_show' => 1]);
        });

        //排除该详情的产品id
        $productId = isset($data['productId']) ? $data['productId'] : '';
        $query->when($productId, function ($query) use ($productId) {
            $query->where('p.platform_product_id', '!=', $productId);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $userId = $data['userId'];
        $query->when($userId, function ($query) use ($delProIds) {
            $query->whereNotIn('p.platform_product_id', $delProIds);
        });

        /* 排序 */
        /* 非登录，非会员：所有vip在下  点击立即申请添加判断，不允许申请
         * 会员登录：vip在哪儿无所谓*/
        if (empty($data['vip_sign'])) { //
            $query->orderBy('is_vip_product', 'asc');
        } else //vip登录 特殊vip产品在最下面
        {
            $query->orderBy('is_special_vip', 'asc');
        }

        /* 排序 */
        $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience', 'p.is_vip']);
        $query->orderBy('p.position_sort', 'asc')
            ->orderBy('p.platform_product_id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 会员对应可以查看的产品ids
     * @status 状态, 1使用中, 0未使用
     * @param array $params
     * @return array
     */
    public static function fetchProductVipIdsByVipTypeId($params = [])
    {
        $productIds = PlatformProductVip::select(['product_id'])
            ->where(['vip_type_id' => $params['userVipType'], 'status' => 1])
            ->pluck('product_id')
            ->toArray();

        return $productIds ? $productIds : [];
    }

    /**
     * 非会员对应可以查看的产品ids
     * @status 状态, 1使用中, 0未使用
     * @param array $params
     * @return array
     */
    public static function fetchProductNoVipIdsByVipTypeId($params = [])
    {
        $productIds = PlatformProductVip::select(['product_id'])
            ->where(['vip_type_id' => $params['userNoVipType'], 'status' => 1])
            ->pluck('product_id')
            ->toArray();

        return $productIds ? $productIds : [];
    }

    /**
     * 用户不想看产品ids
     * @status 状态, 1有效, 0无效
     * @param array $params
     * @return array
     */
    public static function fetchBlackIdsByUserId($params = [])
    {
        $ids = UserProductBlack::select(['product_id'])
            ->where(['user_id' => $params['userId'], 'status' => 1])
            ->pluck('product_id')
            ->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 详情页标签类型表 类型id
     * @status 状态, 1使用中, 0未使用
     * @param string $param
     * @return string
     */
    public static function fetchApprovalConditionTypeId($param = '')
    {
        $typeId = PlatformProductTagType::select('id')
            ->where(['type_nid' => $param, 'status' => 1])
            ->first();

        return $typeId ? $typeId->id : '';
    }

    /**
     * 详情页标签类型表 标签
     * @param array $params
     * @return array
     */
    public static function fetchDetailTags($params = [])
    {
        $tagIds = self::fetchProductTagsIdsOnly($params['productId'], $params['type_id']);
        $tags = self::fetchSeoTagsIdsOnly($tagIds);
        $seoTag = [];
        foreach ($tags as $key => $val) {
            $seoTag[$key]['name'] = isset($val['name']) ? $val['name'] : [];
            $seoTag[$key]['font_color'] = isset($val['font_color']) ? $val['font_color'] : [];
            $seoTag[$key]['boder_color'] = isset($val['boder_color']) ? $val['boder_color'] : [];
            $seoTag[$key]['bg_color'] = isset($val['bg_color']) ? $val['bg_color'] : [];
        }
        return $seoTag;
    }

    /**
     * 第二版 首页今日良心推荐
     * @param array $data
     * @return mixed
     */
    public static function fetchSecondEditionRecommends($data = [])
    {
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->select(
                'p.platform_product_id',
                'p.platform_id',
                'p.platform_product_name',
                'p.product_introduct',
                'p.product_logo',
                'p.loan_min',
                'p.loan_max',
                'p.type_nid',
                'p.is_preference'
            )
            ->orderBy('p.platform_product_id', 'desc')
            ->whereIn('p.platform_product_id', $data['recommendIds'])
            ->limit($data['limit']);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        $productLists = $query->get()->toArray();

        return $productLists;
    }

    /**
     * 查询产品黑名单存在状态
     * @param array $params
     * @return int
     */
    public static function fetchProductBlackStatus($params = [])
    {
        $black = UserProductBlack::select(['status'])
            ->where(['user_id' => $params['userId'], 'product_id' => $params['productId']])
            ->first();

        return $black ? $black->status : 0;
    }

    /**
     * 创建产品黑名单
     * @status 状态, 1有效, 0无效
     * @param array $params
     * @return bool
     */
    public static function updateProductBlack($params = [])
    {
        $query = UserProductBlack::where(['user_id' => $params['userId'], 'product_id' => $params['productId']])
            ->first();
        if (empty($query)) {
            $query = new UserProductBlack();
            $query->created_at = date('Y-m-d H:i:s', time());
            $query->created_ip = Utils::ipAddress();
        }

        $query->user_id = $params['userId'];
        $query->product_id = $params['productId'];
        $query->status = 1;
        $query->updated_at = date('Y-m-d H:i:s', time());
        $query->updated_ip = Utils::ipAddress();
        return $query->save();
    }

    /**
     * 取消产品不想看状态
     * @status 状态, 1有效, 0无效
     * @param array $params
     * @return string
     */
    public static function deleteProductBlack($params = [])
    {
        $query = UserProductBlack::where(['user_id' => $params['userId'], 'product_id' => $params['productId']])
            ->update([
                'status' => 0,
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);

        return $query ? $query : '';
    }

    /**
     * 根据产品id查询产品对应费率
     * @type  类型,1 默认 2 模板
     * @status 状态, 1有效, 0无效
     * @operator 计算方式, 1加法方式, 2利率方式
     * @date_relate  日期相关, 1相关, 0不相关
     * @param $productId
     * @return array
     */
    public static function fetchProductFee($productId)
    {
        $fee = PlatformProductFee::select(['id', 'product_id', 'operator', 'name', 'type_nid', 'value', 'date_relate', 'status', 'remark'])
            ->where(['product_id' => $productId, 'status' => 1, 'type' => 1])
            ->orderBy('position_sort', 'asc')
            ->orderBy('id', 'desc')
            ->get()->toArray();

        return $fee ? $fee : [];
    }

    /**
     * 产品推荐配置类型表
     * @status 状态, 1 有效, 0 无效
     * @param $typeNid
     * @return string
     */
    public static function fetchPlatformProductRecommendTypeIdByNid($typeNid)
    {
        $typeId = PlatformProductRecommendType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $typeId ? $typeId->id : '';
    }

    /**
     * 产品推荐配置类型表
     * @status 状态, 1 有效, 0 无效
     *
     * @param $typeNid
     * @return array
     */
    public static function fetchProductRecommendTypeByNid($typeNid)
    {
        $typeId = PlatformProductRecommendType::select(['id', 'num', 'is_recommend_circulate', 'is_pro_total_circulate'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $typeId ? $typeId->toArray() : [];
    }


    /**
     * 符合typeid的产品id
     * @status 状态, 1 有效, 0 无效
     * @param array $params
     * @return array
     */
    public static function fetchRecommendIdsByTypeId($params = [])
    {
        $productIds = PlatformProductRecommend::select(['product_id'])
            ->where(['type_id' => $params['typeId'], 'status' => 1])
            ->pluck('product_id')
            ->toArray();

        return $productIds ? $productIds : [];
    }

    /**
     * 黑名单产品
     * @param array $params
     * @return array
     */
    public static function fetchProductBlackIdsInfo($params = [])
    {
        $blacks = UserProductBlack::select(['product_id', 'updated_at'])
            ->where(['user_id' => $params['userId']])
            ->whereIn('product_id', $params['mergeBlackIds'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->toArray();

        return $blacks ? $blacks : [];
    }

    /**
     * 不想看所有产品id
     * @param array $params
     * @return array
     */
    public static function fetchProductBlackIds($params = [])
    {
        $blackIds = UserProductBlack::select(['product_id'])
            ->where(['user_id' => $params['userId'], 'status' => 1])
            ->pluck('product_id')
            ->toArray();

        return $blackIds ? $blackIds : [];
    }

    /**
     * 不想看产品列表
     * @param array $params
     * @return array
     */
    public static function fetchProductBlacks($params = [])
    {
        $products = [];
        foreach ($params as $key => $val) {
            $product = ProductFactory::productOne($val['product_id']);
            if ($product) {
                $products[$key]['platform_product_id'] = $product['platform_product_id'];
                $products[$key]['platform_id'] = $product['platform_id'];
                $products[$key]['platform_product_name'] = $product['platform_product_name'];
                $products[$key]['product_logo'] = QiniuService::getProductImgs($product['product_logo'], $val['product_id']);
                $products[$key]['shielding_time'] = $val['updated_at'];
            }
        }

        return array_values($products);
    }

    /**
     * 不想看产品总个数
     * @param array $params
     * @return int
     */
    public static function fetchBlackCountsById($params = [])
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;

        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->whereIn('p.platform_product_id', $params['blackIds']);
        
        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['p.is_main_show' => 1]);
        });

        return $query ? $query->count() : 0;
    }

    /**
     * 在线产品总个数
     * @param array $data
     * @return int
     */
    public static function fetchProductCounts($data = [])
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;

        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->whereIn('p.platform_product_id', $data['productIds']);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        $counts = $query->count();

        return $counts ? $counts : 0;
    }

    /**
     * 获取产品ids
     *
     * @param $typeId
     * @return mixed
     */
    public static function fetchProductVipIds($typeId)
    {
        $data = PlatformProductVip::where(['vip_type_id' => $typeId, 'status' => 1])
            ->pluck('product_id')->toArray();

        return $data ? $data : [];
    }

    /**
     * 验证单一产品是否是会员产品
     *
     * @param array $data
     * @return int
     */
    public static function checkIsVipProduct($data = [])
    {
        //普通产品
        $data['typeId'] = UserVipFactory::getCommonTypeId();
        $ordinary = ProductFactory::fetchIsVipByProId($data);
        //vip产品
        $data['typeId'] = UserVipFactory::getVipTypeId();
        $vip = ProductFactory::fetchIsVipByProId($data);

        if ($vip && !$ordinary) {
            $is_vip = 1;
        } else {
            $is_vip = 0;
        }

        return $is_vip ? $is_vip : 0;
    }

    /**
     * 单一产品会员信息
     *
     * @param array $data
     * @return array
     */
    public static function fetchIsVipByProId($data = [])
    {
        $data = PlatformProductVip::where(['vip_type_id' => $data['typeId'], 'status' => 1, 'product_id' => $data['productId']])
            ->first();

        return $data ? $data->toArray() : [];
    }

    /**
     * @return int
     * 产品今日申请总量
     */
    public static function fetchTodayApplyCount()
    {
        $today = date('Y-m-d');
        $todayApply = DataProductApplyLog::where('create_at', '>=', $today . ' 00:00:00')->where('create_at', '<', $today . ' 23:59:59')->count();

        return $todayApply ? $todayApply : 0;
    }

    /**
     * 获取所有在线产品今日申请量total_today_count总和
     * @return int
     */
    public static function fetchTodayApplyCountByTotalTodayCount()
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;

        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        $count = $query->sum('total_today_count');

        return $count ? $count : 0;
    }

    /**
     * 滑动专题产品
     * @param array $data
     * @return array
     */
    public static function fetchSlideProducts($data = [])
    {
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.max_rate'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $data['productVipIds']);
        //配置推荐产品
        $query->whereIn('p.platform_product_id', $data['productIds']);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //排序
        $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 不想看产品标签流水记录
     * @param array $params
     * @return bool
     */
    public static function createProductBlackTagLog($params = [])
    {
        $log = new UserProductBlackTagLog();
        $log->user_id = $params['userId'];
        $log->product_id = $params['productId'];
        $log->tag_id = isset($params['tagId']) ? 0 : $params['tagId'];
        $log->content = isset($params['content']) ? $params['content'] : '';
        $log->type = $params['type'];
        $log->status = 0;
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        $res = $log->save();

        return $res;
    }

    /**
     * 删除标签
     * @param array $params
     * @return bool
     */
    public static function deletePeoductBlackTags($params = [])
    {
        $deleteTag = UserProductBlackTag::select(['id'])
            ->where(['user_id' => $params['userId'], 'product_id' => $params['productId']])
            ->update([
                'status' => 9,
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);

        return $deleteTag ? $deleteTag : false;
    }

    /**
     * 不想看产品对应标签
     * @param array $params
     * @return array
     */
    public static function fetchBlackConTagIds($params = [])
    {
        $conTagIds = UserProductBlackTag::select(['id', 'tag_id'])
            ->where(['user_id' => $params['userId'], 'product_id' => $params['productId']])
            ->pluck('tag_id')
            ->toArray();

        return $conTagIds ? $conTagIds : [];
    }

    /**
     * 不想看产品标签修改
     * @param array $params
     * @return bool
     */
    public static function updateProductBlackTag($params = [])
    {
        $tag = UserProductBlackTag::select(['id'])
            ->where(['user_id' => $params['userId'], 'product_id' => $params['productId'], 'tag_id' => $params['tagId']])
            ->first();

        if (!$tag) {
            $tag = new UserProductBlackTag();
            $tag->created_at = date('Y-m-d H:i:s', time());
            $tag->created_ip = Utils::ipAddress();
        }

        $tag->user_id = $params['userId'];
        $tag->product_id = $params['productId'];
        $tag->tag_id = isset($params['tagId']) ? $params['tagId'] : 0;
        $tag->content = isset($params['content']) ? $params['content'] : '';
        $tag->type = $params['type'];
        $tag->status = isset($params['status']) ? $params['status'] : 0;
        $tag->updated_at = date('Y-m-d H:i:s', time());
        $tag->updated_ip = Utils::ipAddress();

        return $tag->save();
    }

    /**
     * 详情页标签类型 获取id
     * @param string $typeNid
     * @return int
     */
    public static function fetchProductTagTypeIdByNid($typeNid = '')
    {
        $typeId = PlatformProductTagType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $typeId ? $typeId->id : 0;
    }

    /**
     * 根据标签类型id查询标签ids
     * @param string $typeId
     * @return array
     */
    public static function fetchProductTagsByTagId($typeId = '')
    {
        $tagIds = ProductTag::select(['tag_id'])
            ->where(['type_id' => $typeId, 'status' => 1])
            ->pluck('tag_id')
            ->toArray();

        return $tagIds ? $tagIds : [];
    }

    /**
     * 根据标签ids获取标签数据
     * @param array $ids
     * @return array
     */
    public static function fetchSeoTagsByIds($ids = [])
    {
        $tags = TagSeo::select(['id', 'name', 'font_color', 'boder_color', 'bg_color'])
            ->whereIn('id', $ids)
            ->get()
            ->toArray();

        return $tags ? $tags : [];
    }

    /**
     * 首页推荐产品
     *
     * @param array $data
     * @return array
     */
    public static function fetchRecommendHomeProducts($data = [])
    {
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //所有产品id
        $productIds = $data['productIds'];
        //产品城市关联表中的所有产品id
        $cityProductIds = isset($data['cityProductIds']) ? $data['cityProductIds'] : [];
        //地域对应产品id
        $deviceProductIds = isset($data['deviceProductIds']) ? $data['deviceProductIds'] : [];
        //地域id
        $deviceId = isset($data['deviceId']) ? $data['deviceId'] : '';

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->whereIn('p.platform_product_id', $data['recommendProductIds']);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $data['productVipIds']);

        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['p.is_main_show' => 1]);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //地域筛选
        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });


        $query->addSelect(['p.position_sort', 'p.is_vip']);
        $query->orderBy('p.is_vip', 'asc')
            ->orderBy('p.position_sort', 'asc')
            ->orderBy('p.platform_product_id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 在线产品ids
     * @param array $data
     * @return array
     */
    public static function fetchDayOnlineProductIds($data = [])
    {
        $ids = DataProductDayOnline::select(['platform_product_id'])
            ->where(['created_date' => $data['created_date']])
            ->where(['is_online' => 1])
            ->pluck('platform_product_id')
            ->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 马甲产品唯一标识对应马甲id
     * @param string $nid
     * @return string
     */
    public static function fetchShadowIdByNid($nid = '')
    {
        $id = ShadowCount::select(['id'])->where(['nid' => $nid])->first();

        return $id ? $id->id : '';
    }

    /**
     * 马甲产品ids
     * @param string $shadowId
     * @return array
     */
    public static function fetchShadowProductIds($shadowId = '')
    {
        $ids = PlatformProductPosition::select(['product_id'])
            ->where(['shadow_id' => $shadowId, 'online_status' => 1])
            ->orderBy('position_sort', 'asc')
            ->pluck('product_id')
            ->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 固定长度马甲产品ids
     * @param $shadowId
     * @return array
     */
    public static function fetchShadowProductIdsByLimit($shadowId)
    {
        $ids = PlatformProductPosition::select(['product_id'])
            ->where(['shadow_id' => $shadowId, 'online_status' => 1])
            ->orderBy('position_sort', 'asc')
            ->orderBy('product_id', 'desc')
            ->limit(21)
            ->pluck('product_id')
            ->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 马甲1.0.1版本查询产品列表
     * @param array $data
     * @return array|bool
     */
    public static function fetchProductsShadowByPosotion($data = [])
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;

        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;
        //马甲产品ids
        $shadowProductIds = $data['shadowProductIds'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->whereIn('p.platform_product_id', $shadowProductIds);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        /* 排序 */
        if ($productType == 1) {     //综合指数
            $query->addSelect(['p.position_sort', 'p.is_vip']);
            $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->addSelect(['p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
        } else {
            return false;
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 产品详情 - 相似产品
     * @param array $data
     * @return array
     */
    public static function fetchShadowLikeProducts($data = [])
    {
        //分页 默认显示5条数据
        $pageSize = isset($data['pageSize']) ? $data['pageSize'] : 1;
        $pageNum = isset($data['pageNum']) ? $data['pageNum'] : 5;

        //所有产品id
        $productIds = $data['productIds'];
        //产品城市关联表中的所有产品id
        $cityProductIds = $data['cityProductIds'];
        //地域对应产品id
        $deviceProductIds = $data['deviceProductIds'];
        //地域id
        $deviceId = $data['deviceId'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //马甲产品ids
        $shadowProductIds = $data['shadowProductIds'];
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link',
                'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type',
                'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->whereIn('p.platform_product_id', $shadowProductIds);

        //排除该详情的产品id
        $productId = isset($data['productId']) ? $data['productId'] : '';
        $query->when($productId, function ($query) use ($productId) {
            $query->where('p.platform_product_id', '!=', $productId);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //地域筛选
        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });

        //定位排序
        $condition = implode(',', $data['shadowProductIds']);
        if ($condition) {
            $query->orderByRaw(DB::raw("FIELD(`platform_product_id`, " . $condition . ')'));
        }
        $query->orderBy('p.platform_product_id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 标签规则匹配产品ids
     * @param string $prductId
     * @return array
     */
    public static function fetchProductTagMatch($prductId = '')
    {
        $matchIds = ProductTagMatch::select(['match_product_id'])
            ->where(['product_id' => $prductId, 'status' => 1])
            ->orderBy('weight', 'desc')
            ->orderBy('position_sort', 'asc')
            ->pluck('match_product_id')
            ->toArray();

        return $matchIds ? $matchIds : [];
    }

    /**
     * 产品结算模式关联表
     * @param string $productId
     * @return array
     */
    public static function fetchProductSettleRel($productId = '')
    {
        $typeIds = PlatformProductSettleTypeRel::select(['settle_type_id'])
            ->where(['platform_product_id' => $productId, 'status' => 1])
            ->pluck('settle_type_id')
            ->toArray();

        return $typeIds ? $typeIds : [];
    }

    /**
     * 产品结算模式唯一标识
     * @param array $typeIds
     * @return array
     */
    public static function fetchSettleTypeNidsByIds($typeIds = [])
    {
        $nids = PlatformProductSettleType::select(['type_nid'])
            ->whereIn('id', $typeIds)
            ->where(['status' => 1])
            ->pluck('type_nid')
            ->toArray();

        return $nids ? $nids : [];
    }

    /**
     * 产品id匹配规则
     * 会员+地域+不想看+标签筛选
     *
     * @param array $data
     * @return array
     */
    public static function fetchFilterProductIdsByConditions($data = [])
    {
        //区分会员与非会员的产品ids [求交集]
        $data['userVipType'] = UserVipFactory::fetchUserVipToTypeByUserId($data['userId']);
        $productVipIds = [];
        if ($data['userVipType']) {
            //会员
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }


        //传定位进行筛选,否则不进行筛选
        $deviceProductIdDatas = []; //@todo  1
        if ($data['deviceNum']) { //有用户定位
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);
            //所有产品id
            $productIds = ProductFactory::fetchProductIds();
            //产品城市关联表中的所有产品id
            $cityProductIds = DeviceFactory::fetchCityProductIds();
            //地域对应产品id
            $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);
            $diff = array_diff($productIds, $cityProductIds);
            $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
        }


        //不想看产品ids [求差集]
        $blackIds = ProductFactory::fetchBlackIdsByUserId($data);

        //标签筛选产品
        $matchIds = ProductFactory::fetchProductTagMatch($data['productId']);

        //交集最终需要查询的产品ids
        $intersectIds = $deviceProductIdDatas ? array_intersect($matchIds, $deviceProductIdDatas, $productVipIds) : $productVipIds;
        $intersectIds = array_values($intersectIds);
        //差集
        $diffIds = array_diff($intersectIds, $blackIds);
        return $diffIds ? $diffIds : [];
    }

    /**
     * 需要vip产品标签的产品ids
     * @return array|bool
     */
    public static function fetchDivisionProductIds()
    {
        //vip用户可查看产品ids
        $productVipIds = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        //非vip和用户可查看产品ids
        $productCommonIds = ProductFactory::fetchProductVipIds(UserVipFactory::getCommonTypeId());
        //处理数据
        //会员产品id作为key
        $vipCommonDiffIds = array_diff($productVipIds, $productCommonIds);
        $vipProductIds = isset($vipCommonDiffIds) ? array_flip($vipCommonDiffIds) : [];

        return $vipProductIds ? $vipProductIds : [];
    }

    /**
     * 产品详情 —— 产品特色
     * @param array $data
     * @return array
     */
    public static function fetchProductListsByTagMatchs($data = [])
    {
        //分页 默认显示5条数据
        $pageSize = isset($data['pageSize']) ? $data['pageSize'] : 1;
        $pageNum = isset($data['pageNum']) ? $data['pageNum'] : 5;
        //标签匹配ids
        $matchIds = $data['matchIds'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link',
                'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type',
                'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        $productIds = $data['productIds'];
        //用户可以看产品
        $query->whereIn('p.platform_product_id', $productIds);

        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['p.is_main_show' => 1]);
        });

        //排除该详情的产品id
        $productId = isset($data['productId']) ? $data['productId'] : '';
        $query->when($productId, function ($query) use ($productId) {
            $query->where('p.platform_product_id', '!=', $productId);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //排序
        $query->when($matchIds, function ($query) use ($matchIds) {
            $condition = implode(',', $matchIds);
            $query->orderByRaw(DB::raw("FIELD(`platform_product_id`, " . $condition . ')'));
        });

        //定位排序
        //$query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 速贷大全筛选id集合
     * 会员+地域+不想看
     *
     * @param array $data
     * @return array
     */
    public static function fetchProductOrSearchIds($data = [])
    {
        //区分会员与非会员的产品ids [求交集]
        $data['userVipType'] = UserVipFactory::fetchUserVipToTypeByUserId($data['userId']);
        $productVipIds = [];
        if ($data['userVipType']) {
            //会员
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }


        //传定位进行筛选,否则不进行筛选
        $deviceProductIdDatas = [];
        if ($data['deviceNum']) { //有用户定位  //@todo   2
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);
            //所有产品id
            $productIds = ProductFactory::fetchProductIds();
            //产品城市关联表中的所有产品id
            $cityProductIds = DeviceFactory::fetchCityProductIds();
            //地域对应产品id
            $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);
            $diff = array_diff($productIds, $cityProductIds);
            $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
        }

        //不想看产品ids  并且不影响产品列表排序
        $blackIds = ProductFactory::fetchBlackIdsByUserId($data);
        //不计算进不想看的产品ids
        $blackIdsStr = empty($blackIdsStr) ? [] : explode(',', $blackIdsStr);
        //原来已存在不想看产品ids 与并不计算进不想看的ids求差集
        $finalBlackIds = array_diff($blackIds, $blackIdsStr);

        //交集最终需要查询的产品ids
        $intersectIds = $deviceProductIdDatas ? array_intersect($deviceProductIdDatas, $productVipIds) : $productVipIds;
        //差集
        $diffIds = array_diff($intersectIds, $finalBlackIds);

        return $diffIds ? $diffIds : [];
    }

    /**
     * 速贷大全筛选id集合
     * 会员+地域
     *
     * @param array $data
     * @return array
     */
    public static function fetchVipDeviceProIds($data = [])
    {
        //区分会员与非会员的产品ids [求交集]
        $data['userVipType'] = UserVipFactory::fetchUserVipToTypeByUserId($data['userId']);
        $productVipIds = [];
        if ($data['userVipType']) {
            //会员
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }

        //传定位进行筛选,否则不进行筛选
        $deviceProductIdDatas = [];
        if ($data['deviceNum']) { //有用户定位  //@todo   3
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);
            //所有产品id
            $productIds = ProductFactory::fetchProductIds();
            //产品城市关联表中的所有产品id
            $cityProductIds = DeviceFactory::fetchCityProductIds();
            //地域对应产品id
            $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);
            $diff = array_diff($productIds, $cityProductIds);
            $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
        }

        //交集最终需要查询的产品ids
        $intersectIds = $deviceProductIdDatas ? array_intersect($deviceProductIdDatas, $productVipIds) : $productVipIds;

        return $intersectIds ? $intersectIds : [];
    }

    /**
     * 参与撞库 不符合模式规则或者不符合资质的所有产品id集合
     *
     * @param array $data
     * @return array
     */
    public static function fetchDelQualifyProductIds($data = [])
    {
        $userId = $data['userId'];
        $delProIds = [];
        //CPA模式下产品ids
        $modelProIds = [];
        $modelData = [];

        if ($userId) {
            //1.属于CPA注册模式下的产品不展示
            //参与撞库的所有产品id集合
            $data['is_new_user'] = ProductConstant::IS_NEW_USER;
            $abutProIds = ProductFactory::fetchAbutProductIds($data);
            //CPA注册模式
            $modelNids = ProductConstant::SETTLEMENT_MODEL;
            //注册结算模式对应id
            $modelIds = ProductFactory::fetchSettleIdByNid($modelNids);

            //属于CPA模式的产品ids
            if ($abutProIds) {
                foreach ($abutProIds as $key => $val) { //
                    $modelData[$key]['productId'] = $val;
                    $modelData[$key]['modelIds'] = ProductFactory::fetchSettleProductIds($val);
                }
            }

            if ($modelData) {
                foreach ($modelData as $k => $item) { //
                    if ($item['modelIds'] == $modelIds) {
                        $modelProIds[] = $item['productId'];
                    }
                }
            }

            //不属于CPA模式的产品ids
            $notModelProIds = array_diff($abutProIds, $modelProIds);
            //不符合资质产品ids
            $notQualifyProIds = ProductFactory::fetchNotQualifyProIds($userId, $notModelProIds);

            //求并集
            $delProIds = array_merge($modelProIds, $notQualifyProIds);
        }

        return $delProIds ? $delProIds : [];
    }

    /**
     * 结算模式查询id
     *
     * @param $models
     * @return array
     */
    public static function fetchSettleIdByNid($modelNids)
    {
        $ids = PlatformProductSettleType::select(['id'])
            ->whereIn('type_nid', $modelNids)
            ->where(['status' => 1])
            ->pluck('id')
            ->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 不属于该结算模式查询id
     *
     * @param array $models
     * @return array
     */
    public static function fetchNotSettleIdByNid($models = [])
    {
        $ids = PlatformProductSettleType::select(['id'])
            ->whereNotIn('type_nid', $models)
            ->where(['status' => 1])
            ->pluck('id')
            ->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 产品结算模式关联表，产品id集合
     *
     * @param $modelIds
     * @return array
     */
    public static function fetchSettleTypeRelProidsByTypeIds($modelIds)
    {
        $productIds = PlatformProductSettleTypeRel::select(['platform_product_id'])
            ->whereIn('settle_type_id', $modelIds)
            ->where(['status' => 1])
            ->pluck('platform_product_id')
            ->toArray();

        return $productIds ? $productIds : [];
    }

    /**
     * 撞库产品id集合
     *
     * @param array $data
     * @return array
     */
    public static function fetchAbutProductIds($data = [])
    {
        $productIds = DataProductAccess::select(['platform_product_id'])
            ->where(['user_id' => $data['userId']])
            ->whereIn('is_new_user', $data['is_new_user'])
            ->pluck('platform_product_id')
            ->toArray();

        return $productIds ? $productIds : [];
    }

    /**
     * 不符合资质产品id集合
     *
     * @param string $userId
     * @param array $notModelProIds
     * @return array
     */
    public static function fetchNotQualifyProIds($userId = '', $notModelProIds = [])
    {
        $ids = DataProductAccess::select(['platform_product_id'])
            ->where(['user_id' => $userId, 'qualify_status' => 0])
            ->whereIn('platform_product_id', $notModelProIds)
            ->pluck('platform_product_id')
            ->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 第五版 千人千面产品列表
     *
     * @param $data
     * @return array|bool
     */
    public static function fetchProductsOrSearchsByFifthEdition($data)
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        //身份
        //$indent = isset($data['indent']) ? intval($data['indent']) : 0;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //贷款金额
        $loanAmount = empty($data['loanAmount']) ? [] : explode(',', $data['loanAmount']);
        //贷款期限
        $loanTerm = empty($data['loanTerm']) ? [] : explode(',', $data['loanTerm']);

        //所有产品id
        $productIds = $data['productIds'];
        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $delProIds = $data['delProIds'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $productIds);

        //排除该详情的产品id
        $productId = isset($data['productId']) ? $data['productId'] : '';
        $query->when($productId, function ($query) use ($productId) {
            $query->where('p.platform_product_id', '!=', $productId);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $userId = $data['userId'];
        $query->when($userId, function ($query) use ($delProIds) {
            $query->whereNotIn('p.platform_product_id', $delProIds);
        });


        //借款金额
        $query->when($loanAmount, function ($query) use ($loanAmount) {
            $query->where(function ($query) use ($loanAmount) {
                if (empty($loanAmount[1])) {
                    $query->where('loan_min', '>', $loanAmount[0])->orWhere('loan_max', '>', $loanAmount[0]);
                } else {
                    $query->whereBetween('loan_min', $loanAmount)->orWhereBetween('loan_max', $loanAmount);
                    $query->orWhere(function ($query) use ($loanAmount) {
                        $query->where('loan_min', '<', $loanAmount[0])->where('loan_max', '>', $loanAmount[1]);
                    });
                }
            });
        });

        //借款期限
        $query->when($loanTerm, function ($query) use ($loanTerm) {
            $query->where(function ($query) use ($loanTerm) {
                if (empty($loanTerm[1])) {
                    $query->where('period_min', '>', $loanTerm[0])->orWhere('period_max', '>', $loanTerm[0]);
                } else {
                    $query->whereBetween('period_min', $loanTerm)->orWhereBetween('period_max', $loanTerm);
                    $query->orWhere(function ($query) use ($loanTerm) {
                        $query->where('period_min', '<', $loanTerm[0])->where('period_max', '>', $loanTerm[1]);
                    });
                }
            });
        });

        //贷款类型
        //我需要
        $query->when($loanNeed, function ($query) use ($loanNeed) {
            $loanNeedArr = explode(',', $loanNeed);
            //获取对应的tag_id的标签
            //$loanNeedArr = ProductFactory::fetchTagId($loanNeedArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanNeedArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        //我有
        $query->when($loanHas, function ($query) use ($loanHas) {
            $loanHasArr = explode(',', $loanHas);
            //获取对应的tag_id的标签
            //$loanHasArr = ProductFactory::fetchTagId($loanHasArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanHasArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        if (isset($data['vip_sign']) && $data['vip_sign']) { //vip登录特殊vip在下
            $query->orderBy('is_special_vip', 'asc');
        }

        /* 排序 */
        if ($productType == 1) {     //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience', 'p.is_vip']);
            $query->orderBy('p.position_sort', 'asc')
                ->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->addSelect(['p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
        } else {
            return false;
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 产品结算模式下产品ids
     *
     * @param $abutProIds
     * @param $modelIds
     * @return array
     */
    public static function fetchSettleProductIds($productId = '')
    {
        $ids = PlatformProductSettleTypeRel::select(['settle_type_id'])
            ->where('platform_product_id', $productId)
            ->where(['status' => 1])
            ->pluck('settle_type_id')
            ->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 最终需要展示的产品ids
     * 地域+不想看筛选
     *
     * @param array $data
     * @return array
     */
    public static function fetchNoDiffVipProductOrSearchIds($data = [])
    {
        //无论是会员\非会员  都按会员计算
        $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_DEFAULT);
        $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);

        //传定位进行筛选,否则不进行筛选
        $deviceProductIdDatas = [];
        if ($data['deviceNum']) { //有用户定位   //@todo   4
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);
            if (!empty($deviceId)) {  //
                //所有产品id
                $productIds = ProductFactory::fetchProductIds();
                //产品城市关联表中的所有产品id
                $cityProductIds = DeviceFactory::fetchCityProductIds();
                //地域对应产品id
                $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);
                $diff = array_diff($productIds, $cityProductIds);
                $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
            }
        }

        //不想看产品ids  并且不影响产品列表排序
        $blackIds = ProductFactory::fetchBlackIdsByUserId($data);
        //不计算进不想看的产品ids
        $blackIdsStr = empty($blackIdsStr) ? [] : explode(',', $blackIdsStr);
        //原来已存在不想看产品ids 与并不计算进不想看的ids求差集
        $finalBlackIds = array_diff($blackIds, $blackIdsStr);

        //不想看产品与不符合地域的产品求差集
        $diffIds = array_diff($deviceProductIdDatas, $finalBlackIds);

        //可以看的产品与会员求交集
        $merge = $diffIds ? array_intersect($diffIds, $productVipIds) : $productVipIds;

        return $merge ? $merge : [];
    }

    /**
     * V6 速贷大全
     * 最终需要展示的产品ids
     * 地域+不想看筛选
     *
     * @param array $data
     * @return array
     */
    public static function fetchNoDiffVipProductOrSearchId($data = [])
    {
        //区分是会员\非会员
        if ($data['vip_sign']) {
            //会员
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }
        //传定位进行筛选,否则不进行筛选
        $deviceProductIdDatas = [];
        if ($data['deviceNum']) { //有用户定位   //@todo   4
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);
            if (!empty($deviceId)) {
                //所有产品id
                $productIds = ProductFactory::fetchProductIds();
                //产品城市关联表中的所有产品id
                $cityProductIds = DeviceFactory::fetchCityProductIds();
                //地域对应产品id
                $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);
                $diff = array_diff($productIds, $cityProductIds);
                $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
            }
        }

        //不想看产品ids  并且不影响产品列表排序
        $blackIds = ProductFactory::fetchBlackIdsByUserId($data);
        //不计算进不想看的产品ids
        $blackIdsStr = empty($blackIdsStr) ? [] : explode(',', $blackIdsStr);
        //原来已存在不想看产品ids 与并不计算进不想看的ids求差集
        $finalBlackIds = array_diff($blackIds, $blackIdsStr);

        //不想看产品与不符合地域的产品求差集
        $diffIds = array_diff($deviceProductIdDatas, $finalBlackIds);

        //可以看的产品与会员求交集
        $merge = $diffIds ? array_intersect($diffIds, $productVipIds) : $productVipIds;

        return $merge ? $merge : [];
    }

    /**
     * v7 速贷大全
     * 地域+不想看
     * 每日解锁产品
     *
     * @param array $data
     * @return array
     */
    public static function fetchNoDiffProductOrSearchIds($data = [])
    {
        //区分是会员\非会员
        if ($data['userVipType']) {
            //会员
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }

        //所有产品id
        $productIds = ProductFactory::fetchProductIds();
        //传定位进行筛选,否则不进行筛选
        $deviceProductIdDatas = [];
        if ($data['deviceNum']) { //有用户定位   //@todo   4
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);
            if (!empty($deviceId)) {
                //产品城市关联表中的所有产品id
                $cityProductIds = DeviceFactory::fetchCityProductIds();
                //地域对应产品id
                $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);
                $diff = array_diff($productIds, $cityProductIds);
                $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
            }
        }

        //渠道筛选
        $deliveryEmProIds = [];
        if (isset($data['delivery_sign']) && $data['delivery_sign'] == 1) {
            //与渠道产品求交集
            //无对应渠道产品
            //渠道产品关联中所有产品ids
            $deliveryProAllIds = ProductFactory::fetchProIdsByDeliveryId();
            //去重
            $deliveryProAllIds = array_unique($deliveryProAllIds);
            $deliveryEmProIds = $deliveryProAllIds ? array_diff($productIds, $deliveryProAllIds) : $productIds;
            //渠道对应产品
            if ($data['delivery_id']) {
                //渠道对应产品
                $deliveryProIds = ProductFactory::fetchProIdsByDeliveryId($data['delivery_id']);
                $deliveryEmProIds = array_merge($deliveryProIds, $deliveryEmProIds);
            }
        }

        //不想看产品ids  并且不影响产品列表排序
        $blackIds = ProductFactory::fetchBlackIdsByUserId($data);
        //不计算进不想看的产品ids
        $blackIdsStr = empty($blackIdsStr) ? [] : explode(',', $blackIdsStr);
        //原来已存在不想看产品ids 与并不计算进不想看的ids求差集
        $finalBlackIds = array_diff($blackIds, $blackIdsStr);

        //不想看产品与不符合地域的产品求差集
        $diffIds = array_diff($deviceProductIdDatas, $finalBlackIds);

        //可以看的产品与会员求交集
        $merge = $diffIds ? array_intersect($diffIds, $productVipIds) : $productVipIds;

        $merge = $deliveryEmProIds ? array_intersect($deliveryEmProIds, $merge) : $merge;

        //获取连登解锁产品与可查看产品的交集
        $unlock = ProductFactory::fetchUnlockProducts($data);

        $unlock_data = $unlock ? array_intersect($unlock, $merge) : [];

        if ($data['userVipType']) {
            $unlock_data = ProductStrategy::getVipProIds($data, $unlock_data);
        }

        return $unlock_data ? $unlock_data : [];
    }

    /**
     * 第五版产品列表
     * 非登录，非会员：所有vip在下  点击立即申请添加判断，不允许申请
     * 会员登录：vip在哪儿无所谓
     *
     * @param array $data
     * @return array|bool
     */
    public static function fetchNoDiffVipProductsOrSearchs($data = [])
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        //身份
        //$indent = isset($data['indent']) ? intval($data['indent']) : 0;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //贷款金额
        $loanAmount = empty($data['loanAmount']) ? [] : explode(',', $data['loanAmount']);
        //贷款期限
        $loanTerm = empty($data['loanTerm']) ? [] : explode(',', $data['loanTerm']);

        //所有产品id
        $productIds = $data['productIds'];
        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $delProIds = $data['delProIds'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid', 'p.is_vip_product'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $productIds);

        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['p.is_main_show' => 1]);
        });

        //排除该详情的产品id
        $productId = isset($data['productId']) ? $data['productId'] : '';
        $query->when($productId, function ($query) use ($productId) {
            $query->where('p.platform_product_id', '!=', $productId);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $userId = $data['userId'];
        $query->when($userId, function ($query) use ($delProIds) {
            $query->whereNotIn('p.platform_product_id', $delProIds);
        });


        //借款金额
        $query->when($loanAmount, function ($query) use ($loanAmount) {
            $query->where(function ($query) use ($loanAmount) {
                if (empty($loanAmount[1])) {
                    $query->where('loan_min', '>', $loanAmount[0])->orWhere('loan_max', '>', $loanAmount[0]);
                } else {
                    $query->whereBetween('loan_min', $loanAmount)->orWhereBetween('loan_max', $loanAmount);
                    $query->orWhere(function ($query) use ($loanAmount) {
                        $query->where('loan_min', '<', $loanAmount[0])->where('loan_max', '>', $loanAmount[1]);
                    });
                }
            });
        });

        //借款期限
        $query->when($loanTerm, function ($query) use ($loanTerm) {
            $query->where(function ($query) use ($loanTerm) {
                if (empty($loanTerm[1])) {
                    $query->where('period_min', '>', $loanTerm[0])->orWhere('period_max', '>', $loanTerm[0]);
                } else {
                    $query->whereBetween('period_min', $loanTerm)->orWhereBetween('period_max', $loanTerm);
                    $query->orWhere(function ($query) use ($loanTerm) {
                        $query->where('period_min', '<', $loanTerm[0])->where('period_max', '>', $loanTerm[1]);
                    });
                }
            });
        });

        //贷款类型
        //我需要
        $query->when($loanNeed, function ($query) use ($loanNeed) {
            $loanNeedArr = explode(',', $loanNeed);
            //获取对应的tag_id的标签
            //$loanNeedArr = ProductFactory::fetchTagId($loanNeedArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanNeedArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        //我有
        $query->when($loanHas, function ($query) use ($loanHas) {
            $loanHasArr = explode(',', $loanHas);
            //获取对应的tag_id的标签
            //$loanHasArr = ProductFactory::fetchTagId($loanHasArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanHasArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        /* 排序 */
        /* 非登录，非会员：所有vip在下  点击立即申请添加判断，不允许申请
         * 会员登录：vip在哪儿无所谓*/
        if (empty($data['vip_sign'])) {
            $query->orderBy('is_vip_product', 'asc');
        } else //vip登录 特殊vip产品在最下面
        {
            $query->orderBy('is_special_vip', 'asc');
        }

        if ($productType == 1) {     //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience', 'p.is_vip']);
            $query->orderBy('p.position_sort', 'asc')
                ->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->addSelect(['p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
        } else {
            return false;
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 第七版产品列表
     *
     * @param array $data
     * @return array|bool
     */
    public static function fetchVipProductsOrSearchs($data = [])
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        //身份
        //$indent = isset($data['indent']) ? intval($data['indent']) : 0;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //贷款金额
        $loanAmount = empty($data['loanAmount']) ? [] : explode(',', $data['loanAmount']);
        //贷款期限
        $loanTerm = empty($data['loanTerm']) ? [] : explode(',', $data['loanTerm']);

        //所有产品id
        $productIds = $data['productIds'];

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $delProIds = $data['delProIds'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid', 'p.is_vip_product'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $productIds);

        //排除该详情的产品id
        $productId = isset($data['productId']) ? $data['productId'] : '';
        $query->when($productId, function ($query) use ($productId) {
            $query->where('p.platform_product_id', '!=', $productId);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $userId = $data['userId'];
        $query->when($userId, function ($query) use ($delProIds) {
            $query->whereNotIn('p.platform_product_id', $delProIds);
        });

        //借款金额
        $query->when($loanAmount, function ($query) use ($loanAmount) {
            $query->where(function ($query) use ($loanAmount) {
                if (empty($loanAmount[1])) {
                    $query->where('loan_min', '>', $loanAmount[0])->orWhere('loan_max', '>', $loanAmount[0]);
                } else {
                    $query->whereBetween('loan_min', $loanAmount)->orWhereBetween('loan_max', $loanAmount);
                    $query->orWhere(function ($query) use ($loanAmount) {
                        $query->where('loan_min', '<', $loanAmount[0])->where('loan_max', '>', $loanAmount[1]);
                    });
                }
            });
        });

        //借款期限
        $query->when($loanTerm, function ($query) use ($loanTerm) {
            $query->where(function ($query) use ($loanTerm) {
                if (empty($loanTerm[1])) {
                    $query->where('period_min', '>', $loanTerm[0])->orWhere('period_max', '>', $loanTerm[0]);
                } else {
                    $query->whereBetween('period_min', $loanTerm)->orWhereBetween('period_max', $loanTerm);
                    $query->orWhere(function ($query) use ($loanTerm) {
                        $query->where('period_min', '<', $loanTerm[0])->where('period_max', '>', $loanTerm[1]);
                    });
                }
            });
        });

        //贷款类型
        //我需要
        $query->when($loanNeed, function ($query) use ($loanNeed) {
            $loanNeedArr = explode(',', $loanNeed);
            //获取对应的tag_id的标签
            //$loanNeedArr = ProductFactory::fetchTagId($loanNeedArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanNeedArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        //我有
        $query->when($loanHas, function ($query) use ($loanHas) {
            $loanHasArr = explode(',', $loanHas);
            //获取对应的tag_id的标签
            //$loanHasArr = ProductFactory::fetchTagId($loanHasArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanHasArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        /* 排序 */
        if ($productType == 1) {     //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience', 'p.is_vip']);
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
        } elseif ($productType == 3) { //新上线产品
            $query->addSelect(['p.create_date', 'p.online_at']);
        } elseif ($productType == 4) { //新放款速度
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
        } elseif ($productType == 5) {  //贷款利率
            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
        } elseif ($productType == 6) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
        } else {
            return false;
        }

        $proIds = implode(',', $productIds);
        if ($proIds) {
            $query->orderBy(DB::raw('FIND_IN_SET(platform_product_id, "' . $proIds . '"' . ")"));
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 获取排序后的速贷产品id
     *
     * @param array $data
     * @return array
     */
    public static function fetchSortProIds($data = [], $productIds = [])
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //贷款金额
        $loanAmount = empty($data['loanAmount']) ? [] : explode(',', $data['loanAmount']);
        //贷款期限
        $loanTerm = empty($data['loanTerm']) ? [] : explode(',', $data['loanTerm']);

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $delProIds = $data['delProIds'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $productIds);

        //排除该详情的产品id
        $productId = isset($data['productId']) ? $data['productId'] : '';
        $query->when($productId, function ($query) use ($productId) {
            $query->where('p.platform_product_id', '!=', $productId);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $userId = $data['userId'];
        $query->when($userId, function ($query) use ($delProIds) {
            $query->whereNotIn('p.platform_product_id', $delProIds);
        });


        //借款金额
        $query->when($loanAmount, function ($query) use ($loanAmount) {
            $query->where(function ($query) use ($loanAmount) {
                if (empty($loanAmount[1])) {
                    $query->where('loan_min', '>', $loanAmount[0])->orWhere('loan_max', '>', $loanAmount[0]);
                } else {
                    $query->whereBetween('loan_min', $loanAmount)->orWhereBetween('loan_max', $loanAmount);
                    $query->orWhere(function ($query) use ($loanAmount) {
                        $query->where('loan_min', '<', $loanAmount[0])->where('loan_max', '>', $loanAmount[1]);
                    });
                }
            });
        });

        //借款期限
        $query->when($loanTerm, function ($query) use ($loanTerm) {
            $query->where(function ($query) use ($loanTerm) {
                if (empty($loanTerm[1])) {
                    $query->where('period_min', '>', $loanTerm[0])->orWhere('period_max', '>', $loanTerm[0]);
                } else {
                    $query->whereBetween('period_min', $loanTerm)->orWhereBetween('period_max', $loanTerm);
                    $query->orWhere(function ($query) use ($loanTerm) {
                        $query->where('period_min', '<', $loanTerm[0])->where('period_max', '>', $loanTerm[1]);
                    });
                }
            });
        });

        /* 排序 */
        /* 非登录，非会员：所有vip在下  点击立即申请添加判断，不允许申请
         * 会员登录：vip在哪儿无所谓*/
        if (empty($data['vip_sign'])) {
            $query->orderBy('is_vip_product', 'asc');
        } else //vip登录 特殊vip产品在最下面
        {
            $query->orderBy('is_special_vip', 'asc');
        }

        if ($productType == 1) {     //综合指数
            $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
        } else {
            return false;
        }

        $product = $query->pluck('p.platform_product_id');

        return $product ? $product->toArray() : [];
    }

    /**
     * 申请历史
     * 获取所有申请产品ids
     *
     * @param array $data
     * @return array
     */
    public static function fetchApplyHistoryIdsByUserId($data = [])
    {
        $ids = DataProductApplyHistory::select(['id', 'platform_product_id', 'is_urge', 'created_at', 'user_id', 'platform_id'])
            ->where(['user_id' => $data['userId']])
            ->orderBy('created_at', 'desc')
            ->pluck('platform_product_id')
            ->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 全部是VIP产品总数
     * vip用户与非vip用户可以看见的数据差值
     *
     * @return int|string
     */
    public static function fetchVipProductDiffCounts()
    {
        //vip用户可查看产品ids
        $productVipIds = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        $data['productIds'] = $productVipIds;
        $counts = ProductFactory::fetchProductCounts($data);
        //非vip和用户可查看产品ids
        $productCommonIds = ProductFactory::fetchProductVipIds(UserVipFactory::getCommonTypeId());
        $data['productIds'] = $productCommonIds;
        $commonCounts = ProductFactory::fetchProductCounts($data);
        //vip用户与非vip用户可以看见的数据差值
        $diffCounts = bcsub($counts, $commonCounts);
        if ($diffCounts < 0) {
            $diffCounts = 0;
        }

        return $diffCounts ? $diffCounts : 0;
    }

    /**
     * 全部是VIP产品总数
     * vip用户与非vip用户可以看见的数据差值
     *
     * @return int|string
     */
    public static function fetchVipProductDiffCounts325($fiters)
    {
        $productVipIds = ProductFactory::fetchVipProductIds();
        //会员产品排序
        $data['productIds'] = $productVipIds;

        //过滤城市/设备产品
        $params = [
            'select' => ['platform_product_id', 'terminal_type'],
            'where_in' => [
                'platform_product_id' => $productVipIds,
                'is_delete' => [ProductConstant::PRODUCT_IS_DELETE_UNDELETE, ProductConstant::PRODUCT_IS_DELETE_UNREAL_DELETE],
            ],
        ];
        $PlatformProductIds = PlatformProductFactory::getAll($params);
        $data['productIds'] = self::dealProIdsByTerminalTypeAndLocation($PlatformProductIds, $fiters);

        return count($data['productIds']);
    }

    /**
     * 过滤城市/设备产品
     *
     * @param $PlatformProductIds
     * @param $fiters
     * @return array
     */
    public static function dealProIdsByTerminalTypeAndLocation($PlatformProductIds, $fiters)
    {
        //根据terminalType剔除不符合条件的产品
        $PlatformProductIds = array_filter($PlatformProductIds, function ($item, $k) use ($fiters) {
            $itemTerminalTypes = explode(',', $item['terminal_type']);

            if (in_array(0, $itemTerminalTypes) || in_array($fiters['terminalType'], $itemTerminalTypes)) {
                return true;
            }

            return false;
        }, ARRAY_FILTER_USE_BOTH);

        $unlockLoginProductIds = array_column($PlatformProductIds, 'platform_product_id', 'platform_product_id');

        //根据设备deviceNum剔除不符合条件的产品
        if ($fiters['deviceNum']) { //有用户定位   //@todo   4
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($fiters['deviceNum']);
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

        return $unlockLoginProductIds;
    }

    /**
     * 产品申请第二版
     * 展示所有产品 vip、非vip产品
     * vip产品标识模糊
     *
     * @param array $data
     * @return array
     */
    public static function fetchApplyHistorys($data = [])
    {
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);

        $query = DataProductApplyHistory::select(['id', 'platform_product_id', 'is_urge', 'created_at', 'user_id', 'platform_id'])
            ->where(['user_id' => $data['userId']])
            ->orderBy('created_at', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 获取产品对应的一个标签值
     *
     * @param string $productId
     * @return array|mixed
     */
    public static function fetchTagByProId($productId = '')
    {
        $data['productId'] = $productId;
        //标签类型id
        $data['typeId'] = ProductFactory::fetchProductTagTypeIdByNid(ProductConstant::PRODUCT_TAG_TYPE_LOAN);
        //标签id
        $tagIds = ProductFactory::fetchTagIdByIds($data);
        //标签id替换为标签名称
        $tagName = ProductFactory::fetchTagNameByTagId($tagIds);
        //目前为一维数组
        return $tagName ? $tagName[0] : [];
    }

    /**
     * 标签是否显示功能
     * 根据产品id获取标签id，标签是否显示
     *
     * @param array $data
     * @return array
     */
    public static function fetchTagIdByIds($data = [])
    {
        $proTag = ProductTag::select([
            'is_tag',
            'tag_id',
        ])
            ->where('status', '!=', 9)
            ->where(['type_id' => $data['typeId'], 'platform_product_id' => $data['productId']])
            ->orderBy('position', 'asc')
            ->limit(1)
            ->get()->toArray();
//        dd($proTag);
        return $proTag ? $proTag : [];
    }

    /**
     * 标签
     *
     * @param array $tagIds
     * @return array
     */
    public static function fetchTagNameByTagId($tagIds = [])
    {
        foreach ($tagIds as $key => $val) {
            $tagIds[$key]['tag_name'] = ProductFactory::fetchTagName($val['tag_id']);
        }

        return $tagIds ? $tagIds : [];
    }

    /**
     * 根据tag_id获取tag_name的值
     *
     * @param string $tagId
     * @return string
     */
    public static function fetchTagName($tagId = '')
    {
        $tagName = TagSeo::select(['name'])
            ->where(['id' => $tagId, 'status' => 1])
            ->first();
        return $tagName ? $tagName->name : '';
    }


    /**
     * 首页推荐产品
     * 第二版 优化筛选条件 整合筛选的产品ids为一个字段
     *
     * @param array $data
     * @return array
     */
    public static function fetchRecommendProducts($data = [])
    {
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //所有产品id
        $productIds = $data['productIds'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->whereIn('p.platform_product_id', $data['recommendProductIds']);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $productIds);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        $query->addSelect(['p.position_sort', 'p.is_vip']);
        $query->orderBy('p.is_vip', 'asc')
            ->orderBy('p.position_sort', 'asc')
            ->orderBy('p.platform_product_id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 极速贷推荐类型id
     *
     * @param string $typeNid
     * @return int
     */
    public static function fetchQuickLoanRecomTypeId($typeNid = '')
    {
        $id = QuickloanProductRecommendType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $id ? $id->id : 0;
    }

    /**
     * 极速贷推荐产品ids集合
     *
     * @param string $typeId
     * @return array
     */
    public static function fetchRecomProductIds($typeId = '')
    {
        $ids = QuickloanProductRecommend::select(['product_id'])
            ->where(['type_id' => $typeId, 'status' => 1])
            ->pluck('product_id')
            ->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 极速贷推荐产品列表
     *
     * @param array $data
     * @return array
     */
    public static function fetchRecomProductsByIds($data = [])
    {
        //筛选
        $filters = $data['filters'];
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //是否进行产品下线筛选
        $isDelete = isset($data['isDelete']) ? $data['isDelete'] : 0;

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //推荐产品ids 必须存在
        $query->whereIn('p.platform_product_id', $data['recomProductIds']);
        //所有产品ids 必须存在
        $query->whereIn('p.platform_product_id', $filters['productVipIds']);

        //产品下线筛选
        $query->when($isDelete, function ($query) use ($isDelete) {
            $query->where(['p.is_delete' => 0]);
        });

        //定位筛选  可选
        $deviceProIds = $filters['deviceProductIdDatas'];
        $query->when($data['deviceNum'], function ($query) use ($deviceProIds) {
            $query->whereIn('p.platform_product_id', $deviceProIds);
        });

        //终端类型筛选 可选
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        /* 排序 */
        /* 非登录，非会员：所有vip在下  点击立即申请添加判断，不允许申请
         * 会员登录：vip在哪儿无所谓*/
        if (empty($data['vip_sign'])) { //
            $query->orderBy('is_vip_product', 'asc');
        } else //vip登录 特殊vip产品在最下面
        {
            $query->orderBy('is_special_vip', 'asc');
        }

        //排序
        $query->addSelect(['p.position_sort']);
        $query->orderBy('p.position_sort', 'asc')
            ->orderBy('p.platform_product_id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 筛选条件集合
     * 单一条件判断
     *
     * @param array $data
     * @return array
     */
    public static function fetchFilters($data = [])
    {
        //无论是会员\非会员  都按会员计算
        $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_DEFAULT);
        $data['productVipIds'] = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);

        //定位筛选
        $data['deviceProductIdDatas'] = [];
        if ($data['deviceNum']) { //存在 筛选;不存在 不筛选
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);
            //所有产品id
            $productIds = ProductFactory::fetchProductIds();
            //产品城市关联表中的所有产品id
            $cityProductIds = DeviceFactory::fetchCityProductIds();
            //地域对应产品id
            $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);
            $diff = array_diff($productIds, $cityProductIds);
            $data['deviceProductIdDatas'] = array_merge($diff, $deviceProductIds);
        }

        return $data ? $data : [];
    }

    /**
     * 筛选条件集合
     * 单一条件判断、区分会员|非会员
     *
     * @param array $data
     * @return array
     */
    public static function fetchFiltersDisVip($data = [])
    {
        //区分会员、非会员
        $data['userVipType'] = UserVipFactory::fetchUserVipToTypeByUserId($data['userId']);
        $productVipIds = [];
        if ($data['userVipType']) {
            //会员
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }
        $data['productVipIds'] = $productVipIds;

        //定位筛选
        $data['deviceProductIdDatas'] = $productVipIds;
        if ($data['deviceNum']) { //存在 筛选;不存在 不筛选
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);
            //所有产品id
            $productIds = ProductFactory::fetchProductIds();
            //产品城市关联表中的所有产品id
            $cityProductIds = DeviceFactory::fetchCityProductIds();
            //地域对应产品id
            $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);
            $diff = array_diff($productIds, $cityProductIds);
            $data['deviceProductIdDatas'] = array_merge($diff, $deviceProductIds);
        }

        return $data ? $data : [];
    }

    /**
     * 筛选条件集合
     * 单一条件判断、区分会员|非会员
     * 返回最终产品id集合
     *
     * @param array $data
     * @return array
     */
    public static function fetchFiltersDisVipAndDevice($data = [])
    {
        //区分会员、非会员
        $data['userVipType'] = UserVipFactory::fetchUserVipToTypeByUserId($data['userId']);
        $productVipIds = [];
        if ($data['userVipType']) {
            //会员
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }

        //所有产品id
        $productIds = ProductFactory::fetchProductIds();
        //定位筛选
        $deviceProductIdDatas = [];
        if ($data['deviceNum']) { //存在 筛选;不存在 不筛选
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);
            //产品城市关联表中的所有产品id
            $cityProductIds = DeviceFactory::fetchCityProductIds();
            //地域对应产品id
            $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);
            $diff = array_diff($productIds, $cityProductIds);
            $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
        }

        //渠道筛选
        $deliveryEmProIds = [];
        if (isset($data['delivery_sign']) && $data['delivery_sign'] == 1) {
            //与渠道产品求交集
            //无对应渠道产品
            //渠道产品关联中所有产品ids
            $deliveryProAllIds = ProductFactory::fetchProIdsByDeliveryId();
            //去重
            $deliveryProAllIds = array_unique($deliveryProAllIds);
            $deliveryEmProIds = $deliveryProAllIds ? array_diff($productIds, $deliveryProAllIds) : $productIds;
            //渠道对应产品
            if ($data['delivery_id']) {
                //渠道对应产品
                $deliveryProIds = ProductFactory::fetchProIdsByDeliveryId($data['delivery_id']);
                $deliveryEmProIds = array_merge($deliveryProIds, $deliveryEmProIds);
            }
        }

        $productIds = $deviceProductIdDatas ? array_intersect($productVipIds, $deviceProductIdDatas) : $productVipIds;

        $productIds = $deliveryEmProIds ? array_intersect($deliveryEmProIds, $productIds) : $productIds;

        return $productIds ? $productIds : [];
    }

    /**
     * 合作贷类型表
     * 根据type_nid查询类型数据
     *
     * @param array $datas
     * @return array
     */
    public static function fetchCooperateProductTypeByNid($datas = [])
    {
        $type = CooperateProductType::select(['id', 'is_global', 'limit'])
            ->where(['type_nid' => $datas['typeNid'], 'status' => 1])
            ->first();

        return $type ? $type->toArray() : [];
    }

    /**
     * 合作贷产品
     * 筛选条件：类型id、上下线
     * 排序条件：自定义排序
     *
     * @param array $datas
     * @return array
     */
    public static function fetchProIdsByCoorNid($datas = [])
    {
        $proIds = CooperateProduct::select(['product_id'])
            ->where(['type_id' => $datas['id'], 'is_delete' => 0])
            ->orderBy('position_sort', 'asc')
            ->pluck('product_id')
            ->toArray();

        return $proIds ? $proIds : [];
    }

    /**
     * 合作贷产品列表
     *
     * @param array $data
     * @return array
     */
    public static function fetchCooperateProducts($data = [])
    {
        //筛选
        $filters = isset($data['filters']) ? $data['filters'] : [];
        //定位筛选条件
        $deviceProIds = isset($filters['deviceProductIdDatas']) ? $filters['deviceProductIdDatas'] : [];
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = CooperateProduct::from('sd_cooperate_product as cp')
            ->select(['cp.id', 'cp.url as h5_url', 'cp.abut_switch', 'p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.click_count', 'p.fast_time', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid', 'p.is_vip_product', 'p.official_website as product_official_website', 'pro.value'])
            ->join('sd_platform_product as p', 'p.platform_product_id', '=', 'cp.product_id')
            ->where(['cp.is_delete' => 0, 'cp.type_id' => $data['typeId']])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //定位筛选  可选
        $query->when($data['deviceNum'], function ($query) use ($deviceProIds) {
            $query->whereIn('p.platform_product_id', $deviceProIds);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //排序
        $query->addSelect(['p.position_sort', 'p.is_vip']);
        $query->orderBy('cp.position_sort', 'asc')
            ->orderBy('p.position_sort', 'asc')
            ->orderBy('cp.id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 速贷大全
     * 筛选条件：产品类型、贷款类型、贷款金额、贷款期限、所有产品id、撞库产品id、终端类型、定位
     * 排序条件：非会员所有vip在下、productType排序、
     *
     * @param array $data
     * @return array|bool
     */
    public static function fetchProducrsByFilters($data = [])
    {
        //筛选条件
        $filters = isset($data['filters']) ? $data['filters'] : [];
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        //身份
        //$indent = isset($data['indent']) ? intval($data['indent']) : 0;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //贷款金额
        $loanAmount = empty($data['loanAmount']) ? [] : explode(',', $data['loanAmount']);
        //贷款期限
        $loanTerm = empty($data['loanTerm']) ? [] : explode(',', $data['loanTerm']);

        //所有产品id
        $productIds = $filters['productVipIds'];
        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $delProIds = isset($data['delProIds']) ? $data['delProIds'] : [];
        //定位
        $deviceProIds = isset($filters['deviceProductIdDatas']) ? $filters['deviceProductIdDatas'] : [];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid', 'p.is_vip_product'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //所有产品ids
        $query->whereIn('p.platform_product_id', $productIds);

        //定位筛选  可选
        $query->when($data['deviceNum'], function ($query) use ($deviceProIds) {
            $query->whereIn('p.platform_product_id', $deviceProIds);
        });

        //排除该详情的产品id
        $productId = isset($data['productId']) ? $data['productId'] : '';
        $query->when($productId, function ($query) use ($productId) {
            $query->where('p.platform_product_id', '!=', $productId);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $userId = $data['userId'];
        $query->when($userId, function ($query) use ($delProIds) {
            $query->whereNotIn('p.platform_product_id', $delProIds);
        });


        //借款金额
        $query->when($loanAmount, function ($query) use ($loanAmount) {
            $query->where(function ($query) use ($loanAmount) {
                if (empty($loanAmount[1])) {
                    $query->where('loan_min', '>', $loanAmount[0])->orWhere('loan_max', '>', $loanAmount[0]);
                } else {
                    $query->whereBetween('loan_min', $loanAmount)->orWhereBetween('loan_max', $loanAmount);
                    $query->orWhere(function ($query) use ($loanAmount) {
                        $query->where('loan_min', '<', $loanAmount[0])->where('loan_max', '>', $loanAmount[1]);
                    });
                }
            });
        });

        //借款期限
        $query->when($loanTerm, function ($query) use ($loanTerm) {
            $query->where(function ($query) use ($loanTerm) {
                if (empty($loanTerm[1])) {
                    $query->where('period_min', '>', $loanTerm[0])->orWhere('period_max', '>', $loanTerm[0]);
                } else {
                    $query->whereBetween('period_min', $loanTerm)->orWhereBetween('period_max', $loanTerm);
                    $query->orWhere(function ($query) use ($loanTerm) {
                        $query->where('period_min', '<', $loanTerm[0])->where('period_max', '>', $loanTerm[1]);
                    });
                }
            });
        });

        //贷款类型
        //我需要
        $query->when($loanNeed, function ($query) use ($loanNeed) {
            $loanNeedArr = explode(',', $loanNeed);
            //获取对应的tag_id的标签
            //$loanNeedArr = ProductFactory::fetchTagId($loanNeedArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanNeedArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        //我有
        $query->when($loanHas, function ($query) use ($loanHas) {
            $loanHasArr = explode(',', $loanHas);
            //获取对应的tag_id的标签
            //$loanHasArr = ProductFactory::fetchTagId($loanHasArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanHasArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        /* 排序 */
        /* 非登录，非会员：所有vip在下  点击立即申请添加判断，不允许申请
         * 会员登录：vip在哪儿无所谓*/
        if (empty($data['vip_sign'])) { //
            $query->orderBy('is_vip_product', 'asc');
        } else //vip登录 特殊vip产品在最下面
        {
            $query->orderBy('is_special_vip', 'asc');
        }

        if ($productType == 1) {     //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience', 'p.is_vip']);
            $query->orderBy('p.position_sort', 'asc')
                ->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->addSelect(['p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
        } else {
            return false;
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 会员独家
     * 最终需要展示的会员产品ids
     * 地域筛选
     *
     * @param array $data
     * @return array
     */
    public static function fetchNoDiffVipExclusiveProductOrSearchIds($data = [])
    {
        //无论是会员\非会员  都按会员计算
        $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_DEFAULT);
        $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        //非会员产品
        $ordinary['userNoVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
        $productNoVipIds = ProductFactory::fetchProductNoVipIdsByVipTypeId($ordinary);
        //会员产品
        $productVipIds = array_diff($productVipIds, $productNoVipIds);

        //所有产品在线品台下的
        $productIds = ProductFactory::fetchProductIds();
        //传定位进行筛选,否则不进行筛选
        $deviceProductIdDatas = [];
        if ($data['deviceNum']) { //有用户定位
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);//北京
            //产品城市关联表中的所有产品id
            $cityProductIds = DeviceFactory::fetchCityProductIds();//411有城市配置的产品
            //地域对应产品id
            $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);//20个北京的产品
            $diff = array_diff($productIds, $cityProductIds);//没有配置的,全国
            $deviceProductIdDatas = array_merge($diff, $deviceProductIds);//全国跟20个北京产品
        }

        //渠道筛选
        $deliveryEmProIds = [];
        if (isset($data['delivery_sign']) && $data['delivery_sign'] == 1) {
            //与渠道产品求交集
            //无对应渠道产品
            //渠道产品关联中所有产品ids
            $deliveryProAllIds = ProductFactory::fetchProIdsByDeliveryId();
            //去重
            $deliveryProAllIds = array_unique($deliveryProAllIds);
            $deliveryEmProIds = $deliveryProAllIds ? array_diff($productIds, $deliveryProAllIds) : $productIds;
            //渠道对应产品
            if ($data['delivery_id']) {
                //渠道对应产品
                $deliveryProIds = ProductFactory::fetchProIdsByDeliveryId($data['delivery_id']);
                $deliveryEmProIds = array_merge($deliveryProIds, $deliveryEmProIds);
            }
        }

        //不想看产品ids  并且不影响产品列表排序
        $blackIds = ProductFactory::fetchBlackIdsByUserId($data);
        //不计算进不想看的产品ids
        $blackIdsStr = empty($blackIdsStr) ? [] : explode(',', $blackIdsStr);
        //原来已存在不想看产品ids 与并不计算进不想看的ids求差集
        $finalBlackIds = array_diff($blackIds, $blackIdsStr);

        //不想看产品与不符合地域的产品求差集
        $diffIds = array_diff($deviceProductIdDatas, $finalBlackIds);

        //可以看的产品与会员求交集
        $merge = $diffIds ? array_intersect($diffIds, $productVipIds) : $productVipIds;

        $merge = $deliveryEmProIds ? array_intersect($deliveryEmProIds, $merge) : $merge;
//        dd($merge);
        return $merge ? $merge : [];
    }

    /**
     * landing推荐产品
     * 限制2个
     *
     * @param array $params
     * @return array
     */
    public static function fetchRecommendLandingProducts($params = [])
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $products = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.type_nid', 'p.product_h5_url'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->whereIn('p.platform_product_id', $params)
            ->limit(2)
            ->get()->toArray();

        return $products ? $products : [];
    }

    /**
     * 马甲产品排序位置
     *
     * @param array $data
     * @return string
     */
    public static function fetchShadowProductPosition($data = [])
    {
        $position = PlatformProductPosition::select(['position_sort'])
            ->where(['product_id' => $data['productId'], 'shadow_nid' => $data['shadowNid'], 'online_status' => 1])
            ->first();

        return $position ? $position->position_sort : '99';
    }

    /**
     * 解锁连登产品id集合
     * 条件：sd_banner_unlock_login主键id，状态【1开启，0关闭】
     *
     * @param string $unlockLoginId
     * @return array
     */
    public static function fetchProductUnlockLoginByLoginId($unlockLoginId = '')
    {
        $productIds = ProductUnlockLoginRel::select(['product_id'])
            ->where(['unlock_login_id' => $unlockLoginId, 'status' => 1])
            ->pluck('product_id')
            ->toArray();

        return $productIds ? $productIds : [];
    }

    /**
     * 解锁连登产品个数
     *
     * @param string $productIds
     * @return mixed
     */
    public static function fetchProductUnlockLoginCount($productIds = '')
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link',
                'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type',
                'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //筛选条件
        $query->whereIn('p.platform_product_id', $productIds);

        return $query->count();
    }

    /**
     * 解锁联登产品列表
     *
     * @param array $data
     * @return array
     */
    public static function fetchUnlockLoginProducts($data = [])
    {
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid', 'p.is_vip_product'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //地域+区分会员产品
        $query->whereIn('p.platform_product_id', $data['productIds']);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //排序
        $condition = implode(',', $data['productIds']);
        $query->when($condition, function ($query) use ($condition) {
            $query->orderByRaw(DB::raw("FIELD(`platform_product_id`, " . $condition . ')'));
        });


        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * V2 解锁联登产品列表
     *
     * @param array $data
     * @param $finalProductIds
     * @return array
     */
    public static function fetchUnlockLoginProductsV2($data = [], $finalProductIds)
    {
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid', 'p.is_vip_product'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //地域+区分会员产品
        $query->whereIn('p.platform_product_id', $finalProductIds);

        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['p.is_main_show' => 1]);
        });

        //排序
        $condition = implode(',', $finalProductIds);
        $query->when($condition, function ($query) use ($condition) {
            $query->orderByRaw(DB::raw("FIELD(`platform_product_id`, " . $condition . ')'));
        });


        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();
        $productArr = array_column($productArr, null, 'platform_product_id');
//        dd($productArr);

        //按照$finalProductIds排序
        $productArrFinal = [];
        foreach ($finalProductIds as $item) {
            $productArrFinal[] = $productArr[$item];
        }

        $product['list'] = $productArrFinal;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 热门推荐列表展示产品
     * 条件：用户连登天数，用户类型
     *
     * @param array $datas
     * @return array
     */
    public static function fetchRecommendProductsByUserUnlocLogin($datas = [])
    {
        //符合筛选条件的产品
        $filterProIds = isset($datas['productIds']) ? $datas['productIds'] : [];
        //热门推荐不可见产品
        $recomNoProIds = isset($datas['recomNoProIds']) ? $datas['recomNoProIds'] : [];
        //热门推荐可见产品
        $recomProIds = array_diff($filterProIds, $recomNoProIds);
        //用户连登可见产品
        $userProIds = ProductFactory::fetchUserUnloLoginProIds($datas);
        //列表展示产品 用户连登可见产品∩热门推荐可见产品
        $listProIds = array_intersect($userProIds, $recomProIds);

        $products['listProIds'] = $listProIds ? $listProIds : [];
        $products['recomProIds'] = $recomProIds ? $recomProIds : [];

        return $products ? $products : [];
    }

    /**
     * 根据用户最大连登天数返回可见连登产品ids
     *
     * @param array $datas
     * @return array
     */
    public static function fetchUserUnloLoginProIds($datas = [])
    {
        //连登标识集合
        $params['unloSign'] = BannerStrategy::fetchBannerUnlockLoginNids($datas['recommendSign']);
        //可见连登天数下对应的广告id
        $params['userUnloCount'] = $datas['vip_sign'] == 1 ? UserConstant::USER_CONTINUE_LOGIN_DAYS : $datas['userUnloCount'];
        $bannerIds = BannersFactory::fetchBannerUnlockLoginIdsByUserLoginCount($params);
        //用户可见连登产品
        $userProIds = ProductUnlockLoginRel::select(['product_id'])
            ->whereIn('unlock_login_id', $bannerIds)
            ->where(['status' => 1])
            ->pluck('product_id')->toArray();

        return $userProIds ? $userProIds : [];
    }

    /**
     * 对产品ids进行排序处理
     *
     * @param array $proIds
     * @return array
     */
    public static function fetchProIdsByPosition($proIds = [])
    {
        $proIds = PlatformProduct::select(['platform_product_id'])
            ->whereIn('platform_product_id', $proIds)
            ->orderBy('position_sort', 'asc')
            ->orderBy('platform_product_id', 'desc')
            ->pluck('platform_product_id');

        return $proIds ? $proIds->toArray() : [];
    }

    /**
     * 热门推荐产品列表
     * 与用户连登天数、点击立即申请排序有关
     *
     * @param array $data
     * @return mixed
     */
    public static function fetchRecommendsByUserAndRedis($data = [])
    {
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid', 'p.is_vip_product', 'p.position_sort'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->whereIn('p.platform_product_id', $data['productIds']);

        //根据终端类型筛选产品
        $terminalType = $data['terminalType'];
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //排序
        $query->limit($data['num'])->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');

        /* 分页start */
        $productArr = $query->get()->toArray();

        $products = DateUtils::pageInfo($productArr, $pageSize, $pageNum);
        return $products;
    }

    /**
     * 获取解锁产品
     * @return array
     */
    public static function fetchUnlockProducts($data = [])
    {
        $products = ProductUnlockLoginRel::where('status', 1)->whereIn('unlock_login_id', $data['unlock_data'])->pluck('product_id');

        return $products ? $products->toArray() : [];
    }

    /**
     * 轮播开始时间、结束时间
     *
     * @return array
     */
    public static function fetchCirculateDateByDay()
    {
        $day = date('Y-m-d', time());
        $time = date('Y-m-d H:i:s', time());
        $circuDates = ProductCirculateDatetime::select('start_time', 'end_time')
            ->where('end_time', '>=', $time)
            ->where('start_time', '<=', $time)
            ->where(['created_date' => $day])
            ->first();

        return $circuDates ? $circuDates->toArray() : [];
    }

    /**
     * 结算正常产品ids [10000,19999]
     *
     * @return array
     */
    public static function fetchValueProductIdsByPosition($data = [])
    {
        //内部产品排序区间
        $positions = ProductConstant::PRODUCT_VALUE_POSTIONS;

        //查询在线产品
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.click_count', 'p.fast_time', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid', 'p.is_vip_product', 'p.position_sort'])
            ->where(['p.is_delete' => 0])
            ->whereBetween('p.position_sort', $positions)
            ->pluck('p.platform_product_id');

        return $query ? $query->toArray() : [];
    }

    /**
     * 内部产品
     *
     * @return array
     */
    public static function fetchInnerProductIds()
    {
        $inners = PlatformProductInner::select(['product_id'])
            ->where(['status' => 1])
            ->pluck('product_id')->toArray();

        return $inners ? $inners : [];
    }

    /**
     * 限量产品
     *
     * @is_delete  0为未删除，1为删除,2为假删除
     * @return array
     */
    public static function fetchLimitProductIdsByIsDelete()
    {
        //查询在线产品
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.click_count', 'p.fast_time', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid', 'p.is_vip_product', 'p.position_sort'])
            ->where(['p.is_delete' => 2])
            ->pluck('p.platform_product_id');

        return $query ? $query->toArray() : [];
    }

    public static function fetchProductIdsByValAndInnerAndVip($data = [])
    {
        //区分是会员\非会员
        if ($data['userVipType']) {
            //会员
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }

        //传定位进行筛选,否则不进行筛选
        $deviceProductIdDatas = [];
        if ($data['deviceNum']) //有用户定位   //@todo   4
        {
            //根据设备id获取城市id [求交集]
            $deviceId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($data['deviceNum']);
            if (!empty($deviceId)) {
                //所有产品id
                $productIds = ProductFactory::fetchProductIds();
                //产品城市关联表中的所有产品id
                $cityProductIds = DeviceFactory::fetchCityProductIds();
                //地域对应产品id
                $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);
                $diff = array_diff($productIds, $cityProductIds);
                $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
            }
        }

        //不想看产品ids  并且不影响产品列表排序
        $blackIds = ProductFactory::fetchBlackIdsByUserId($data);
        //不计算进不想看的产品ids
        $blackIdsStr = empty($blackIdsStr) ? [] : explode(',', $blackIdsStr);
        //原来已存在不想看产品ids 与并不计算进不想看的ids求差集
        $finalBlackIds = array_diff($blackIds, $blackIdsStr);

        //不想看产品与不符合地域的产品求差集
        $diffIds = array_diff($deviceProductIdDatas, $finalBlackIds);

        //可以看的产品与会员求交集
        $merge = $diffIds ? array_intersect($diffIds, $productVipIds) : $productVipIds;

        //获取连登解锁产品与可查看产品的交集
        $unlock = ProductFactory::fetchUnlockProducts($data);

        $unlock_data = $unlock ? array_intersect($unlock, $merge) : [];

        if ($data['userVipType']) $unlock_data = ProductStrategy::getVipProIds($data, $unlock_data);

        return $unlock_data ? $unlock_data : [];
    }

    /**
     * 对产品列表进行独立模块排序
     *
     * @param $productIds
     * @param $dynamicIds
     * @return array
     */
    public static function fetchSortProductIds($data, $productIds, $dynamicIds)
    {
        $productType = isset($data['productType']) ? $data['productType'] : 1;

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->whereIn('platform_product_id', $productIds)
            ->whereIn('platform_product_id', $dynamicIds)
            ->where(['pro.key' => $key]);


        /* 排序 */
        if ($productType == 1) {     //综合指数
            $query->orderBy('position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->orderBy('success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->orderBy('online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
        } else {
            $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        }

        $proIds = $query->pluck('platform_product_id');

        return $proIds ? $proIds->toArray() : [];
    }

    /**
     * 纯会员用户可查看的产品ids
     *
     * @return array
     */
    public static function fetchVipProductIds()
    {
        //vip用户可查看产品ids
        $productVipIds = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        //非vip用户可查看产品ids
        $productCommonIds = ProductFactory::fetchProductVipIds(UserVipFactory::getCommonTypeId());
        //纯会员产品ids
        $vipProIds = array_diff($productVipIds, $productCommonIds);

        return $vipProIds ? $vipProIds : [];
    }

    /**
     * 产品数据 V8
     *
     * @param array $data
     * @return array
     */
    public static function fetchProductsOrSearchsByConditions($data = [])
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        //身份
        //$indent = isset($data['indent']) ? intval($data['indent']) : 0;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //贷款金额
        $loanAmount = empty($data['loanAmount']) ? [] : explode(',', $data['loanAmount']);
        //贷款期限
        $loanTerm = empty($data['loanTerm']) ? [] : explode(',', $data['loanTerm']);

        //所有产品id
        $productIds = $data['finalProIds'];

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $delProIds = $data['delProIds'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid', 'p.is_vip_product', 'p.is_delete'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.is_delete', [0, 2])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $productIds);

        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['p.is_main_show' => 1]);
        });

        //排除该详情的产品id
        $productId = isset($data['productId']) ? $data['productId'] : '';
        $query->when($productId, function ($query) use ($productId) {
            $query->where('p.platform_product_id', '!=', $productId);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $userId = $data['userId'];
        $query->when($userId, function ($query) use ($delProIds) {
            $query->whereNotIn('p.platform_product_id', $delProIds);
        });

        //借款金额
        $query->when($loanAmount, function ($query) use ($loanAmount) {
            $query->where(function ($query) use ($loanAmount) {
                if (empty($loanAmount[1])) {
                    $query->where('loan_min', '>', $loanAmount[0])->orWhere('loan_max', '>', $loanAmount[0]);
                } else {
                    $query->whereBetween('loan_min', $loanAmount)->orWhereBetween('loan_max', $loanAmount);
                    $query->orWhere(function ($query) use ($loanAmount) {
                        $query->where('loan_min', '<', $loanAmount[0])->where('loan_max', '>', $loanAmount[1]);
                    });
                }
            });
        });

        //借款期限
        $query->when($loanTerm, function ($query) use ($loanTerm) {
            $query->where(function ($query) use ($loanTerm) {
                if (empty($loanTerm[1])) {
                    $query->where('period_min', '>', $loanTerm[0])->orWhere('period_max', '>', $loanTerm[0]);
                } else {
                    $query->whereBetween('period_min', $loanTerm)->orWhereBetween('period_max', $loanTerm);
                    $query->orWhere(function ($query) use ($loanTerm) {
                        $query->where('period_min', '<', $loanTerm[0])->where('period_max', '>', $loanTerm[1]);
                    });
                }
            });
        });

        //贷款类型
        //我需要
        $query->when($loanNeed, function ($query) use ($loanNeed) {
            $loanNeedArr = explode(',', $loanNeed);
            //获取对应的tag_id的标签
            //$loanNeedArr = ProductFactory::fetchTagId($loanNeedArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanNeedArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        //我有
        $query->when($loanHas, function ($query) use ($loanHas) {
            $loanHasArr = explode(',', $loanHas);
            //获取对应的tag_id的标签
            //$loanHasArr = ProductFactory::fetchTagId($loanHasArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanHasArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        /* 排序 */
//        if ($productType == 1) {     //综合指数
//            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience', 'p.is_vip']);
//        } elseif ($productType == 2) {  //成功率
//            $query->addSelect(['p.success_rate']);
//        } elseif ($productType == 3) { //新上线产品
//            $query->addSelect(['p.create_date', 'p.online_at']);
//        } elseif ($productType == 4) { //新放款速度
//            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
//        } elseif ($productType == 5) {  //贷款利率
//            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
//        } elseif ($productType == 6) { //平均额度
//            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
//        } else {
//            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience', 'p.is_vip']);
//        }

        $proIds = implode(',', $productIds);
        if ($proIds) {
            $query->orderByRaw(DB::raw("FIELD(`p`.`platform_product_id`, " . $proIds . ')'));
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * 对产品列表进行独立模块排序
     *
     * @param $productIds
     * @param $dynamicIds
     * @return array
     */
    public static function fetchSortProductIds325($dynamicIds, $proAllIds = [])
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
//            ->whereIn('platform_product_id', $productIds)
            ->whereIn('platform_product_id', $dynamicIds)
            ->whereIn('p.is_delete', [ProductConstant::PRODUCT_IS_DELETE_UNDELETE, ProductConstant::PRODUCT_IS_DELETE_UNREAL_DELETE])
            ->where(['pro.key' => $key]);

        $query->when($proAllIds, function ($query) use ($proAllIds) {
            $query->where(function ($query) use ($proAllIds) {
                $query->whereIn('p.platform_product_id', $proAllIds);
            });
        });

        /* 排序 */
        $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');

        $proIds = $query->pluck('platform_product_id');

        return $proIds ? $proIds->toArray() : [];
    }

    /**
     * 根据productIds,terminalType取产品数据 325
     *
     * @param $productIds
     * @param $terminalType
     * @return array
     */
    public static function fetchProductsOrSearchsByConditions325($productIds, $terminalType, $proAllIds = [])
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid', 'p.is_vip_product', 'p.position_sort'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->whereIn('p.is_delete', [ProductConstant::PRODUCT_IS_DELETE_UNDELETE, ProductConstant::PRODUCT_IS_DELETE_UNREAL_DELETE])
            ->whereIn('p.platform_product_id', $productIds);

        //速贷之家主包标识
        $ua = Utils::fetchUserAgentParam();
        $query->when($ua, function ($query) use ($ua) {
            $query->where(['p.is_main_show' => 1]);
        });

        //根据终端类型筛选产品
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        $query->when($proAllIds, function ($query) use ($proAllIds) {
            $query->where(function ($query) use ($proAllIds) {
                $query->whereIn('p.platform_product_id', $proAllIds);
            });
        });

        $productArr = $query
            ->get()->toArray();

        $productInfos = $productArr;

        return $productInfos ?? [];
    }

    /**
     * 产品详情信息
     *
     * @param string $productId
     * @return array
     */
    public static function fetchProductInfoByProId($productId = '')
    {
        $productObj = PlatformProduct::select(['is_delete'])
            ->where(['platform_product_id' => $productId])
            ->first();

        return $productObj ? $productObj->toArray() : [];
    }

    /**
     * 得到所有产品和端的对应关系
     * @return array
     */
    public static function fetchProductTerminal(){
        $data = PlatformProduct::select(['platform_product_id','terminal_type'])
                                 ->get()
                                 ->toArray();
        return $data ? $data : [];
    }

    /**
     * 根据端得到产品id
     * @return array
     */
    public static function fetchProductIdByTerminal(){
        $data = PlatformProduct::select(['platform_product_id','terminal_type'])
            ->get()
            ->toArray();
        return $data ? $data : [];
    }

    /**
     * 根据解锁阶段得到产品id
     * 解锁阶段【1:新用户，2:连登1天,3:连登2天,4:连登3天】
     * @return array
     */
    public static function fetchProductIdByUnlockStage($type=1){
        $data = PlatformProduct::select(['platform_product_id'])
            ->where(['unlock_stage'=>$type])
            ->pluck('platform_product_id')
            ->toArray();
        return $data ? $data : [];
    }

    /**
     * 纯会员用户可查看的产品ids
     * @param array $vip_type_ids
     * @return array
     */
    public static function fetchVipProductIdsByVipTypeIds($vip_type_ids)
    {
        $data = PlatformProductVip::select(['vip_type_id','product_id'])
            ->where(['status' => 1])
            ->whereIn('vip_type_id',$vip_type_ids)
            ->get()
            ->toArray();

        return $data ? $data : [];
    }

    /**
     * 获取产品分时展示配置
     * @param array $vip_type_ids
     * @return array
     */
    public static function fetchProductShowTimes($productIds,$terminalType)
    {
        $data = ProductLog::select([
                                    'product_id',
                                    'online_type',
                                    'total_online_start',
                                    'total_online_end',
                                    $terminalType.'_online_start',
                                    $terminalType.'_online_end'
                                    ])
                            ->whereIn('product_id',$productIds)
                            ->get()
                            ->toArray();

        return $data ? $data : [];
    }

    /**
     * @param array $productIds
     * @param array $tagIds
     * @param int $typeId
     * 根据标签过滤产品
     */
    public static function fetchProductIdByTagId($productIds,$tagIds,$typeId)
    {
        $data = ProductTag::select(['platform_product_id'])
                            ->whereIn('platform_product_id', $productIds)
                            ->whereIn('tag_id', $tagIds)
                            ->where(['type_id' => $typeId])
                            ->where(['status' => 1])
                            ->pluck('platform_product_id')->toArray();

        return $data ? array_unique($data) : [];
    }

    /**
     * @param array $productIds
     * @param array $tagIds
     * @param string $terminalType
     * 得到产品对应端的到量信息
     */
    public static function fetchProductLimitInfo($terminalType)
    {
        $data = PlatformProductPortRel::select([
                                            'product_id',
                                            $terminalType.'_status'
                                        ])
                                        //->whereIn('product_id',$productIds)
                                        ->get()
                                        ->toArray();

        return $data ? $data : [];
    }

    /**
     * @param array $productIds
     * 得到产品id对应的渠道信息
     */
    public static function fetchProductDeliveryInfo($productIds, $deliveryId)
    {
        $data = PlatformProductDeliverys::select(['product_id'])
            ->whereIn('product_id',$productIds)
            ->where(['is_delete' => 1])
            ->where(['delivery_id' => $deliveryId])
            ->pluck('product_id')
            ->toArray();

        $res = PlatformProductDeliverys::select('product_id')
            ->groupBy('product_id')
            ->whereIn('product_id', $productIds)
            ->where(['is_delete' => 1])
            ->pluck('product_id')
            ->toArray();

        return array_merge($data, array_diff($productIds, $res));
    }

    /**
     * 根据产品ID批量获取产品信息
     * @param array $ids
     * @return array
     *
     */
    public static function productIds(array $ids)
    {
        $productIds = PlatformProduct::from('sd_platform_product')
            ->whereIn('platform_product_id', $ids)
            ->whereIn('is_delete', [0, 2])
            ->get()
            ->toArray();

        return $productIds ? $productIds : [];
    }

    /**
     * 得到用户行为产品配置时刻
     * @param array $productIds
     */
    public static function fetchBehaviorProductInfo()
    {
        $data = ProductBehaviorDatetime::select([
                                            'start_time',
                                            'end_time'
                                         ])
                                         ->where(['status'=>0])
                                         ->get()
                                         ->toArray();
        return $data ? $data : [];
    }

    /**
     * 得到所有用户行为产品
     * @param array $productIds
     */
    public static function fetchBehaviorProductIds()
    {
        $data = PlatformProduct::select([
                                    'platform_product_id'
                                 ])
                                 ->where(['is_behavior'=>1])
                                 ->pluck('platform_product_id')
                                 ->toArray();

        return $data ? $data : [];
    }

    /*
     * 渠道对应产品
     *
     * @param string $deliveryId
     * @return array
     */
    public static function fetchProIdsByDeliveryId($deliveryId = '')
    {
        $query = PlatformProductDeliverys::select(['product_id'])
            ->where(['is_delete' => 1]);

        $query->when($deliveryId, function ($query) use ($deliveryId) {
            $query->where(['delivery_id' => $deliveryId]);
        });

        $proIds = $query->pluck('product_id')
                        ->toArray();

        return $proIds ? $proIds : [];
    }


    /**
     * 渠道课件产品id
     *
     * @param array $data
     * @return array
     */
    public static function fetchDeliveryProIdsByDeliveryId($data = [])
    {
        //所有产品id
        $productIds = ProductFactory::fetchProductIds();
        //无对应渠道产品
        //渠道产品关联中所有产品ids
        $deliveryProAllIds = ProductFactory::fetchProIdsByDeliveryId();
        //去重
        $deliveryProAllIds = array_unique($deliveryProAllIds);
        $deliveryEmProIds = $deliveryProAllIds ? array_diff($productIds, $deliveryProAllIds) : $productIds;
        //渠道对应产品
        if ($data['delivery_id']) {
            //渠道对应产品
            $deliveryProIds = ProductFactory::fetchProIdsByDeliveryId($data['delivery_id']);
            $deliveryEmProIds = array_merge($deliveryProIds, $deliveryEmProIds);
        }

        return $deliveryEmProIds ? $deliveryEmProIds : [];
    }

    /**
     * 根据主包是否可见得到产品
     *
     * @param int $is_main_show
     * @param array $deleteStatus
     * @return array
     */
    public static function fetchProIdsByIsMainShow($is_main_show = 1, array $deleteStatus = [0, 2])
    {
        $data = PlatformProduct::select(['platform_product_id',])
                                 ->whereIn('is_delete', $deleteStatus)
                                 ->where(['is_main_show' => $is_main_show])
                                 ->pluck('platform_product_id')
                                 ->toArray();
        return $data ? $data : [];
    }

    /**
     * 得到产品的位置配置要求
     *
     * @param int $is_main_show
     * @return array
     */
    public static function fetchProductPositionSortRel()
    {
        $sortIds = PlatformProductPositionSort::select(['id'])
                                               ->where(['status' => 1])
                                               ->pluck('id')
                                               ->toArray();


        if(!empty($sortIds)){
            $data = PlatformProductPositionSortRel::select(['sort_id','product_id'])
                                                   ->where(['status' => 1])
                                                   ->whereIn('sort_id',$sortIds)
                                                   ->get()
                                                   ->toArray();
            return $data ? $data : [];
        } else {
            return [];
        }
    }

    /**
     * 得到优质推荐方式
     *
     * @return array
     */
    public static function fetchRecommendPattern()
    {
        $data = PlatformProductConfig::select(['recommend_pattern'])
            ->where(['id' => 1])
            ->pluck('recommend_pattern')
            ->toArray();
        return $data ? $data[0] : [];
    }

    /**
     * 获取正常的平台id
     *
     * @return array
     */
    public static function fetchPlatformIds()
    {
        $onlinePlatformId = Platform::select('platform_id')
            ->where(['is_delete' => 0, 'online_status' => 1])
            ->pluck('platform_id')
            ->toArray();

        return $onlinePlatformId ?: [];
    }
}
