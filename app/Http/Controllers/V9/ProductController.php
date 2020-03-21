<?php

namespace App\Http\Controllers\V9;
use App\Constants\BannersConstant;
use App\Constants\UserConstant;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\UserVipFactory;
use App\Services\Lists\Filter\Filter;
use App\Strategies\BannerStrategy;
use App\Strategies\PushStrategy;
use Illuminate\Http\Request;
use App\Helpers\RestResponseFactory;
use App\Helpers\Utils;
use App\Helpers\RestUtils;
use App\Helpers\DateUtils;
use App\Http\Controllers\Controller;
use App\Services\Lists\Base;
use App\Services\Lists\SubSet\Items\MemberProduct;
use App\Services\Lists\User;
use App\Services\Lists\UserList\UserListFactory;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\ProductStrategy;
use App\Strategies\OauthStrategy;
use App\Models\Factory\ProductSearchFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\FavouriteFactory;
use App\Models\Factory\ProductPropertyFactory;
use App\Models\Chain\Apply\RealnameApply\DoRealnameApplyHandler;
use App\Constants\ProductConstant;
use App\Events\V1\DataProductEvent;
use DB;

/**
 * 产品模块
 * Class ProductController
 * @package App\Http\Controllers\V8
 *
 */
class ProductController extends Controller
{
    /**
     * 速贷大全列表、筛选
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        //$input = $request->all();

        //用户id
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        //用户手机号
        $mobile = isset($request->user()->mobile) ? $request->user()->mobile : '';

        //产品数据类型（3综合排序 4最新产品 5点击最多）
        $listType = (int) $request->input('listType', 3);

        $params = [];
        $params['keyword'] = $request->input('keyword', '');
        $params['vipProduct'] = (int) $request->input('vipProduct', 0);

        $baseTypeList = ['3'=>Base::TYPE_MAIN,'4'=>Base::TYPE_MAIN_NEW,'5'=>Base::TYPE_MAIN_CLICK];

        if (!in_array($listType,array_keys($baseTypeList))) {
            $listType = 3;
        }

        $baseType = $baseTypeList[$listType];

        //终端类型
        $terminalType = (int) $request->input('_os_type', 0);

        //分页数据
        $limit = (int) $request->input('pageNum', 10);
        $page = (int) $request->input('pageSize', 1);

        $deviceId = $request->input('_device_id', '');

        // 版本兼容
        $terminalType = (int) $request->input('terminalType', $terminalType);
        $params['productType'] = (int) $request->input('productType', 1);
        $params['loanNeed'] = $request->input('loanNeed', '');
        $params['loanHas'] = $request->input('loanHas', '');
        $params['loanAmount'] = $request->input('loanAmount', '');
        $params['loanTerm'] = $request->input('loanTerm', '');
        $deviceId = $request->input('deviceId', $deviceId);

        try {
            //拿到产品id集合
            $user = new User($userId, $terminalType, $deviceId);
            $isVip = $user->isVip;
            $user->isVip = $params['vipProduct'] == 1 ? 1 : $user->isVip;
            $userList = UserListFactory::factory($baseType);
            $data = $userList->setUser($user)
                             ->setPage($page, $limit)
                             ->setParams($params)
                             ->getData();



            if (!empty($params['keyword'])) {
                $objUser = $request->user();
                ProductSearchFactory::createSearchLog([
                    'mobile' => $objUser->mobile ?? '',
                    'username' => $objUser->username ?? '',
                ], [
                    'userId' => $userId,
                    'productName' => $params['keyword'],
                ]);
            }

            //返回结果集
            $res['list'] = [];
            $res['pageCount'] = $data->totalPage;
            $res['is_vip'] = $isVip;

            // 取vip产品数需要更改 isVip 为 1
            $pids = (new MemberProduct())->getData();
            $userObj = clone $user;
            $userObj->isVip = 1;
            $validPids = (new Filter())->setProductIds($pids)->setUser($userObj)->filter()->getValidProductIds();
            $res['product_vip_count'] = count($validPids);
            $res['product_diff_count'] = $res['product_vip_count'];

            if (!empty($data->data)) {
                $res['list'] = Product::getK($data->data);
                $res['list'] = ProductFactory::tagsLimitOneToProducts($res['list']);
                $res['list'] = $this->handleProductLists(['mobile'=>$mobile,'list'=>$res['list']], $isVip, $params['vipProduct']);

                $limitProducts = Product::limitProducts($user->terminalType);
                if (!empty($limitProducts)) {
                    foreach ($res['list'] as & $list) {
                        if (in_array($list['platform_product_id'], $limitProducts)) {
                            $list['is_delete'] = 2;
                        }
                    }
                }
            }

            if (!empty($res['list'])) {
                //曝光统计事件监听
                $dataProductEvent = ['userId'=>$userId,'deviceNum'=>$deviceId];
                $dataProductEvent['exposureProIds'] = implode(',', array_column($res['list'], 'platform_product_id'));
                event(new DataProductEvent($dataProductEvent));
            }

            // 兼容 <=3.2.5 版本
            $res['list_sign'] = 1;
            $errorCode = empty($res['list']) ? 1500 : 0;

            return RestResponseFactory::ok($res, null, $errorCode);
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
        //$input = $request->all();

        //用户id
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        //用户手机号
        $mobile= isset($request->user()->mobile) ? $request->user()->mobile : '';

        //终端类型
        $terminalType = (int) $request->input('_os_type', 0);

        //分页数据
        $limit = (int) $request->input('pageNum', 10);
        $page = (int) $request->input('pageSize', 1);

        //用户设备id
        $deviceId = $request->input('_device_id', '');

        // 版本兼容
        $terminalType = (int) $request->input('terminalType', $terminalType);
        $deviceId = $request->input('deviceId', $deviceId);

        try {

            //返回结果集
            $res['qualityProduct'] = [];
            $res['list'] = [];

            $user = new User($userId, $terminalType, $deviceId);

            //获取最优产品id
            $userList = UserListFactory::factory(Base::TYPE_GOOD);
            $qualityProductId = $userList->setUser($user)->getData();

            if (!empty($qualityProductId->data)) {
                $res['qualityProduct'] = Product::getK($qualityProductId->data);
                $res['qualityProduct'] = ProductFactory::tagsLimitOneToProducts($res['qualityProduct']);
                $res['qualityProduct'] = $this->handleProductLists(['mobile'=>$mobile,'list'=>$res['qualityProduct']]);
                $res['qualityProduct'] = array_values($res['qualityProduct']);
            }

            //获取热门推荐产品id集合
            $userList = UserListFactory::factory(Base::TYPE_HOT);
            $data = $userList->setUser($user)
                             ->setPage($page, $limit)
                             //->setParams(['excludeProduct' => array_values($qualityProductId->data)])
                             ->getData();

            $res['pageCount'] = $data->totalPage;

            if (!empty($data->data)) {
                $res['list'] = Product::getK($data->data);
                $res['list'] = ProductFactory::tagsLimitOneToProducts($res['list']);
                $res['list'] = $this->handleProductLists(['mobile'=>$mobile,'list'=>$res['list']]);

                $limitProducts = Product::limitProducts($user->terminalType);
                if (!empty($limitProducts)) {
                    foreach ($res['list'] as & $list) {
                        if (in_array($list['platform_product_id'], $limitProducts)) {
                            $list['is_delete'] = 2;
                        }
                    }
                }
            }

            //曝光统计事件监听
            $dataProductEvent = ['userId'=>$userId,'deviceNum'=>$deviceId];
            if (!empty($res['list'])) {
                $exposureProIds = array_column($res['list'], 'platform_product_id');
                if (!empty($res['qualityProduct'])) {
                    $exposureProIds[] = $res['qualityProduct'][0]['platform_product_id'];
                }
                $dataProductEvent['exposureProIds'] = implode(',', array_unique($exposureProIds));
                event(new DataProductEvent($dataProductEvent));
            }

            // 兼容 <=3.2.5 版本
            //$res['list_sign'] = 1;
            $errorCode = empty($res['list']) ? 1500 : 0;

            return RestResponseFactory::ok($res, null, $errorCode);
        } catch (\Exception $e) {
            logError($e->getMessage(), $e->getTraceAsString());
            return RestResponseFactory::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 产品详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request)
    {
        $data = $request->all();
        $data['productId'] = $request->input('productId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['userId'] = $userId;

        // 版本兼容
        $data['_os_type'] = (int) $request->input('terminalType', $data['_os_type']);

        //请求端 1 ios 2 安卓 3 h5
        if(empty($data['_os_type'])){
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1000), 1000);
        }

        //产品详情
        $data['info'] = ProductFactory::productOneFromProNothing($data['productId']);
        if (empty($data['info'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        //下款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        $loanSpeed = ProductPropertyFactory::fetchPropertyValue($data['productId'], $key);
        $data['loanSpeed'] = empty($loanSpeed) ? '3600' : $loanSpeed;

        //审批条件标签
        $approval_condition = ProductConstant::PRODUCT_DETAIL_APPROVAL_CONDITION;
        $condition['type_id'] = ProductFactory::fetchApprovalConditionTypeId($approval_condition);
        $condition['productId'] = $data['productId'];
        $data['condition_tags'] = ProductFactory::fetchDetailTags($condition);

        //信用贴士标签
        $credit_tips = ProductConstant::PRODUCT_DETAIL_CREDIT_TIPS;
        $tips['type_id'] = ProductFactory::fetchApprovalConditionTypeId($credit_tips);
        $tips['productId'] = $data['productId'];
        $data['tips_tags'] = ProductFactory::fetchDetailTags($tips);

        //手机号
        $data['mobile'] = UserFactory::fetchMobile($data['userId']);

        //认证用户真是姓名
        $user = UserIdentityFactory::fetchUserRealInfo($data['userId']);
        $data['realname'] = isset($user['name']) ? $user['name'] : '';

        //是否是vip产品
        $data['is_vip_product'] = ProductFactory::checkIsVipProduct($data);

        //整合数据
        $product = ProductStrategy::getDetailProductDatas($data);

        //判断是否收藏产品
        $product['sign'] = FavouriteFactory::collectionProducts($data['userId'], $data['productId']);

        //用户信息
        $user = UserFactory::fetchUserNameAndMobile($userId);

        //获取产品信息
        $products = ProductFactory::productOneFromProNothing($data['productId']);

        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryIdToNull($userId);

        //获取渠道信息
        $deliverys = DeliveryFactory::fetchDeliveryArray($deliveryId);

        //访问产品详情记录流水表
        ProductFactory::createProductLog($userId, $data, $user, $products, $deliverys);

        $limitProducts = Product::limitProducts($data['_os_type']);
        if (!empty($limitProducts)) {
            if (in_array($product['platform_product_id'], $limitProducts)) {
                $product['is_delete'] = 2;
            }
        }

        return RestResponseFactory::ok($product);
    }

    /**
     * 认证 & 获取甲方链接接口
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function apply(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        $mobile = $request->user()->mobile;
        $data['userId'] = $userId;
        $data['cacheSign'] = $request->input('cacheSign', 0);
        $data['productId'] = isset($data['productId']) ? intval($data['productId']) : 0;

        // 版本兼容
        $data['_os_type'] = (int) $request->input('terminalType', $data['_os_type']);
        $data['_app_version'] = $request->input('_app_version', '');

        $osType = $data['_os_type'];
        $appVersion = $data['_app_version'];

        //限量产品提示语
        $limitProducts = Product::limitProducts($data['_os_type']);
        if (!empty($limitProducts) && in_array($data['productId'], $limitProducts)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1803), 1803);
        }

        $platformWebsite = PlatformFactory::fetchProductWebsite($data['productId']);

        if (!empty($platformWebsite)) {

            //请求端 1 ios 2 安卓 3 h5
            if(empty($data['_os_type'])){
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1000), 1000);
            }

            //用户是否已实名认证
            $realname = UserIdentityFactory::fetchUserRealIdentityInfo($data['userId']);

            //身份认证
            if (strpos($platformWebsite['is_authen_terminal'],strval($data['_os_type']))!==false) {
                if (empty($realname)) {
                    if ($osType == 2 && $appVersion == '3.2.6') {
                        // TODO 该版本安卓有问题，跳过资格认证
                        $h5url = $platformWebsite['product_h5_url'] ?? '';
                        return RestResponseFactory::ok(['attest'=>0,'qualify'=>1,'url'=>$h5url]);
                    } else {
                        return RestResponseFactory::ok(['attest'=>0,'qualify'=>false,'url'=>'']);
                    }
                }
            }

            if ($osType == 2 && $appVersion == '3.2.6') {
                // TODO 该版本安卓有问题，跳过资格认证
            } else {
                //资格认证
                if ($platformWebsite['sex']!=2) {
                    if (!empty($realname)) {
                        if ($realname['sex']!=$platformWebsite['sex']) {
                            return RestResponseFactory::ok(['attest'=>1,'qualify'=>0,'url'=>'']);
                        }
                    } else {
                        return RestResponseFactory::ok(['attest'=>0,'qualify'=>false,'url'=>'']);
                    }
                }

                if ($platformWebsite['age_min']!=0 || $platformWebsite['age_max']!=0) {
                    if (!empty($realname)) {

                        $age = Utils::getAge($realname['birthday']);

                        if ($age<$platformWebsite['age_min'] || $age>$platformWebsite['age_max']) {
                            return RestResponseFactory::ok(['attest'=>1,'qualify'=>0,'url'=>'']);
                        }
                    } else {
                        return RestResponseFactory::ok(['attest'=>0,'qualify'=>false,'url'=>'']);
                    }
                }
            }
        } else {
            //暂无产品数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1803), 1803);
        }

        //添加日志，存储前端数据
        logInfo('v9_products_apply' . $mobile, ['data' => $data]);

        //获取用户手机号
        $user = UserFactory::fetchUserById($userId);

        //数据处理
        $data = OauthStrategy::getOauthProductDatas($data, $user, $platformWebsite);

        //申请借款责任链
        $data['_skip_apply_log'] = 0;

        /*
        //ios有一个jidaiguanjia渠道的包有问题，gaea拿不到数据，只能用php来记录申请
        if ($data['_os_type'] == 1 && isset($data['_app_version']) && ($data['_app_version'] == '3.2.6')
            && isset($data['_user_agent']) && strpos(trim($data['_user_agent']),'jidaiguanjia') === 0) {
            $data['_skip_apply_log'] = 0;
        }
        */

        $re = new DoRealnameApplyHandler($data);

        $res = $re->handleRequest();

        logInfo('apply-redis',['data'=>$data]);

        //点击的产品id存redis
        if ($data['cacheSign'] == 1) CacheFactory::putProductIdToCache($data);

        //甲方资格认证
        if (!empty($res['is_list'])) {
            if ($osType == 2 && $appVersion == '3.2.6') {
                // TODO 该版本安卓有问题，跳过资格认证
            } else {
                return RestResponseFactory::ok(['attest'=>1,'qualify'=>-1,'url'=>'']);
            }
        }

        //根据配置开关，拼接sign参数,加密请求时间，用于链接有效期校验
        if (!empty($res['url'])) {

            $appkey = platformFactory::fetchPlatformAppkey($data['platformId']);

            $res['url'] = Utils::addSignToUrl($appkey,$res['url']);

        } else {
            //暂无产品数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1803), 1803);
        }

        return RestResponseFactory::ok(['attest'=>1,'qualify'=>1,'url'=>$res['url']]);
    }

    /**
     * 速贷大全与热门推荐返回前端之前的数据处理
     */
    private static function handleProductLists($data = [], $userIsVip = 0, $isVipList = 0)
    {
        //手机号
        $mobile = isset($data['mobile']) ? $data['mobile'] : '';

        $res = [];

        if (!empty($data['list'])) {

            foreach ($data['list'] as $key => $val) {

                //平台id
                $res[$key]['platform_id'] = $val['platform_id'];

                //平台产品id
                $res[$key]['platform_product_id'] = $val['platform_product_id'];

                //产品名称
                $res[$key]['platform_product_name'] = $val['platform_product_name'];

                //产品简介
                $res[$key]['product_introduct'] = Utils::removeHTML($val['product_introduct']);

                //产品logo
                $res[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);

                //产品标签
                $res[$key]['tag_name'] = $val['tag_name'];

                //是否显示标签
                $res[$key]['is_tag'] = isset($val['is_tag']) ? intval($val['is_tag']) : 0;

                //额度范围
                $res[$key]['loan_min'] = $val['loan_min'];
                $res[$key]['loan_max'] = $val['loan_max'];
                $loan_min = DateUtils::formatIntToThous($val['loan_min']);
                $loan_max = DateUtils::formatIntToThous($val['loan_max']);
                $res[$key]['quota'] = $loan_min . '~' . $loan_max;

                //期限范围
                $res[$key]['interest_alg'] = $val['interest_alg'];
                $period_min = ProductStrategy::formatDayToMonthByInterestalg($val['interest_alg'], $val['period_min']);
                $period_max = ProductStrategy::formatDayToMonthByInterestalg($val['interest_alg'], $val['period_max']);
                $res[$key]['term'] = $period_min . '~' . $period_max;

                //放款速度
                $res[$key]['loan_speed'] = ProductStrategy::formatLoanSpeed('3600') . '小时';

                //利率
                $res[$key]['interest_rate'] = $val['min_rate'] . '%';

                //是否是速贷优选产品
                $res[$key]['is_preference'] = isset($val['is_preference']) ? $val['is_preference'] : 0;

                //加密手机号
                $res[$key]['mobile'] = ProductStrategy::fetchEncryptMobile(['mobile'=>$mobile]);

                //对接标识
                $res[$key]['type_nid'] = isset($val['type_nid']) ? strtolower($val['type_nid']) : '';

                //假删除
                $res[$key]['is_delete'] = isset($val['is_delete']) ? intval($val['is_delete']) : 0;

                // shit
                $res[$key]['is_dim_product'] = $isVipList == 1 && $userIsVip == 0 ? 1 : 0;
            }
        }

        return $res;
    }

    public function seriesLoginWindow(Request $request)
    {
        return RestResponseFactory::ok([]);

        $bannerId = $request->input('unlockLoginId', '');//325之后取unlock表新数据
        $userId = $request->user()->sd_user_id ?? '';

        //用户最大连登天数
        $data = UserFactory::fetchUserUnlockLoginTotalByUserId($userId);

        //****运营维护一套产品，但标识可以不同，则标识相关信息取325，产品相关数据取旧版****
        $bannerTypeId325 = BannersFactory::fetchUnlockLoginTypeIdByNid(BannersConstant::BANNER_UNLOCK_LOGIN_TYPE_325);
        if ($bannerId) {
            $bannerUnlock = BannersFactory::fetchBannerUnlockLoginById($bannerId);
        } else {
            $position = [];
            $position['type_id'] = $bannerTypeId325;
            $position['login_count'] = isset($data['login_count']) ? $data['login_count'] : 0;
            //根据用户最大连登天数，判断下一期展示产品
            $bannerUnlock = BannersFactory::fetchBannerUnlockLoginByDesc($position);
        }

        $res = [];
        $res['login_count'] = $data['login_count'];
        $res['near_login_count'] = $data['near_login_count'];
        $res['login_total'] = $data['login_total'];
        $res['need_login_count'] = intval(bcsub($bannerUnlock['unlock_day'], $data['login_count']));
        //本期解锁产品数
        $res['unlock_pro_num'] = $unlockLoginProductNums[$mapUnlockLoginId[$bannerId ?: $bannerUnlock['id']]] ?? 0;
        // 产品列表置顶描述 再连登N天，解锁Q1款产品  Q1=本期对应解锁产品数
        $res['product_list_desc'] = '再连登' . $res['need_login_count'] . '天，解锁' . ($unlockLoginProductNums[$mapUnlockLoginId[$bannerId ?: $bannerUnlock['id']]] ?? 0) . '款产品';
        //判断是否展示速贷大全顶部文案 【0不展示，1展示】
        $res['is_show_desc'] = 1;
        $res['login_count'] = $userUnlockLoginCount;

        return RestResponseFactory::ok($res);
    }


    /**
     * vip 产品数
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function vipCount(Request $request)
    {
        //用户id
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        //终端类型
        $terminalType = (int) $request->input('_os_type', 0);

        $deviceId = $request->input('_device_id', '');

        // 版本兼容
        $terminalType = (int) $request->input('terminalType', $terminalType);
        $deviceId = $request->input('deviceId', $deviceId);

        //拿到产品id集合
        $user = new User($userId, $terminalType, $deviceId);
        $user->isVip = 1;
        $pids = (new MemberProduct())->getData();
        $validPids = (new Filter())->setProductIds($pids)->setUser($user)->filter()->getValidProductIds();
        $count = count($validPids);

        $params = [];
        $params['product_vip_count'] = (int) $count;
        $params['product_diff_count'] = (int) $count;

        return RestResponseFactory::ok($params);
    }
}