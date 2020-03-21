<?php

namespace App\Models\Factory;

use App\Constants\BannersConstant;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\Banner;
use App\Models\Orm\BannerConfig;
use App\Models\Orm\BannerConfigType;
use App\Models\Orm\BannerType;
use App\Models\Orm\BannerUnlockLogin;
use App\Models\Orm\BannerUnlockLoginType;
use App\Models\Orm\CreditCardBanner;
use App\Models\Orm\CreditCardBannerType;
use App\Models\Orm\DataBannerLog;
use App\Models\Orm\DataBannerUnlockLoginLog;
use App\Models\Orm\DataUserRegionLog;
use App\Services\Core\Store\Qiniu\QiniuService;

class BannersFactory extends AbsModelFactory
{

    /**
     * @param $typeNid
     * @return string
     * 广告分类id
     */
    public static function fetchTypeId($typeNid)
    {
        $typeId = BannerType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $typeId ? $typeId->id : '';
    }

    /**
     * 获取首页banber数据
     * @param $typeId
     * @return array
     */
    public static function fetchBanners($typeId)
    {
        //type_id 1 广告，status 1 存在
        $time = date('Y-m-d H:i:s', time());
        $bannerList = Banner::where('endtime', '>', $time)
            ->where(['status' => 1, 'type_id' => $typeId])
            ->orderBy('position')
            ->limit(5)
            ->select('id', 'src', 'app_url', 'h5_link', 'name', 'nid', 'web_switch')
            ->get()->toArray();
        return $bannerList ? $bannerList : [];
    }

    /**
     * 热门贷款节日图片
     */
    public static function fetchBannerConfig()
    {
        $config = BannerConfig::select(['src'])
            ->where(['position' => 2, 'status' => 1])
            ->first();
        return $config ? $config->src : '';
    }

    /**
     * @param $adNum
     * @return array
     * 获取首页分类专题&热门贷款数据  不区分上下线
     */
    public static function fetchCashBannersNoStatus($adNum)
    {
        $cashData = CreditCardBanner::where(['type_id' => $adNum])
            ->orderBy('position', 'asc')
            ->select(['src', 'app_link', 'h5_link', 'title', 'id'])
            ->get();

        return $cashData ? $cashData->toArray() : [];
    }

    /**
     * @param $param
     * @return mixed
     * 获取首页分类专题&热门贷款数据
     */
    public static function fetchCashBanners($adNum)
    {
        $cashData = CreditCardBanner::where(['type_id' => $adNum])
            ->where('status', '<>', 9)
            ->where(['ad_status' => 0])
            ->orderBy('position', 'asc')
            ->select(['id', 'type_nid', 'src', 'app_link', 'h5_link', 'title', 'subtitle', 'web_switch'])
            ->get();

        return $cashData ? $cashData->toArray() : [];
    }

    /**
     * 限制个数
     * @param $adNum
     * @return array
     */
    public static function fetchCashBannersByLimit($datas = [])
    {
        $cashData = CreditCardBanner::where(['type_id' => $datas['type_id']])
            ->where('status', '<>', 9)
            ->where(['ad_status' => 0])
            ->orderBy('position', 'asc')
            ->limit($datas['limit'])
            ->select(['id', 'type_nid', 'src', 'app_link', 'h5_link', 'title', 'subtitle', 'web_switch'])
            ->get();

        return $cashData ? $cashData->toArray() : [];
    }

    /**
     * @param $cashData
     * 判断产品是否下线 下线修改状态
     */
    public static function updateBannerCreditCardStatus($cashData)
    {
        if (empty($cashData)) {
            return false;
        }

        foreach ($cashData as $key => $value) {
            $product = ProductFactory::productOne($value['app_link']);
            if (empty($product)) {
                CreditCardBanner::where(['id' => $value['id'], 'ad_status' => 0])->update(['ad_status' => 1]);
            }
        }

        return true;
    }

    /**
     * @param $typeId
     * @param $status
     * @return array
     * 分类专题图片类型是否存在
     */
    public static function fetchspecialsCategory($typeNid, $status)
    {
        $category = CreditCardBannerType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => $status])
            ->first();

        return $category ? $category->id : '';
    }

    /**
     * @param $typeNid
     * @param $status
     * @return string
     * 广告图片类型是否存在
     */
    public static function fetchBannersCategory($typeNid, $status)
    {
        $category = BannerType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => $status])
            ->first();

        return $category ? $category->id : '';
    }

    /**
     * @param $status
     * @param $typeId
     * @return array
     * 首页速贷专题  跳转规则与banner一致
     */
    public static function fetchSubjects($status, $typeId)
    {
        $time = date('Y-m-d H:i:s', time());
        $subjects = Banner::where(['status' => $status, 'type_id' => $typeId])
            ->where('endtime', '>', $time)
            ->orderBy('position')
            ->select('id', 'src', 'app_url', 'h5_link', 'name', 'subname')
            ->get()->toArray();
        return $subjects ? $subjects : [];
    }

    /**
     * 现金信用卡广告
     * 根据id查询单条信息
     *
     * @param string $id
     * @return array
     */
    public static function fetchBannerCreditCardInfoById($id = '')
    {
        $card = CreditCardBanner::select(['id', 'type_id', 'type_nid', 'name', 'title', 'subtitle', 'url', 'app_link', 'h5_link', 'product_list', 'position'])
            ->where(['id' => $id])
            ->first();

        return $card ? $card : [];
    }

    /**
     * 图片设置图片类型表
     * 根据nid获取主键id
     *
     * @param string $nid
     * @return int
     */
    public static function fetchBannerConfigTypeIdByNid($nid = '')
    {
        $id = BannerConfigType::select(['id'])
            ->where(['type_nid' => $nid, 'status' => 1])
            ->first();

        return $id ? $id->id : 0;
    }

    /**
     * 配置图片
     * 极速贷
     *
     * @param string $id
     * @return string
     */
    public static function fetchBannerConfigImgById($id = '')
    {
        $img = BannerConfig::select(['src'])
            ->where(['type_id' => $id, 'status' => 1])
            ->limit(1)
            ->first();

        return $img ? QiniuService::getImgs($img->src) : '';
    }

    /**
     * 根据广告id获取广告信息
     *
     * @param string $id
     * @return array
     */
    public static function fetchBannerById($id = '')
    {
        $banner = Banner::select()
            ->where(['status' => 1, 'id' => $id])
            ->first();

        return $banner ? $banner->toArray() : [];
    }

    /**
     * 广告点击流水统计
     *
     * @param array $params
     * @return bool
     */
    public static function createBannerLog($params = [], $deliverys = [])
    {
        $banner = isset($params['banner']) ? $params['banner'] : [];
        $log = new DataBannerLog();
        $log->user_id = $params['userId'];
        $log->banner_id = isset($banner['id']) ? $banner['id'] : 0;
        $log->nid = isset($banner['nid']) ? $banner['nid'] : '';
        $log->name = isset($banner['name']) ? $banner['name'] : '';
        $log->position = isset($banner['position']) ? $banner['position'] : 0;
        $log->app_url = isset($banner['app_url']) ? $banner['app_url'] : '';
        $log->h5_link = isset($banner['h5_link']) ? $banner['h5_link'] : '';
        $log->device_id = isset($params['deviceId']) ? $params['deviceId'] : '';
        $log->channel_id = $deliverys['id'];
        $log->channel_title = $deliverys['title'];
        $log->channel_nid = $deliverys['nid'];
        $log->shadow_nid = $params['shadowNid'];
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * 区域点击统计
     *
     * @param array $params
     * @return bool
     */
    public static function createUserRegionLog($params = [], $deliverys = [])
    {
        $log = new DataUserRegionLog();
        $log->user_id = isset($params['userId']) ? $params['userId'] : 0;
        $log->device_id = isset($params['deviceId']) ? $params['deviceId'] : '';
        $log->click_source = isset($params['clickSource']) ? $params['clickSource'] : '';
        $log->title = isset($params['title']) ? Utils::removeSpace($params['title']) : '';
        $log->channel_id = $deliverys['id'];
        $log->channel_title = $deliverys['title'];
        $log->channel_nid = $deliverys['nid'];
        $log->shadow_nid = $params['shadowNid'];
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * 广告连登解锁类型表查询主键id
     * 条件：唯一标识，状态存在
     *
     * @param string $nid
     * @return string
     */
    public static function fetchUnlockLoginTypeIdByNid($nid = '')
    {
        $id = BannerUnlockLoginType::select(['id'])
            ->where(['nid' => $nid, 'status' => 1])
            ->first();

        return $id ? $id->id : '';
    }

    /**
     * 广告连登解锁配置信息
     * 条件：状态，类型id
     *
     * @param string $typeId
     * @return array
     */
    public static function fetchBannerUnlockLoginByTypeId($typeId = '')
    {
        $unlocks = BannerUnlockLogin::select(['id', 'unlock_title', 'unlock_subtitle', 'unlock_day', 'succ_unlock_img', 'fail_unlock_img', 'position', 'unlock_pro_num'])
            ->where(['status' => 1, 'type_id' => $typeId])
            ->where('nid', '!=', BannersConstant::BANNER_UNLOCK_LOGIN_NID_UNLOCK_NEW)
            ->orderBy('position', 'asc')
            ->get()->toArray();

        return $unlocks ? $unlocks : [];
    }

    /**
     * 325 广告连登解锁配置信息
     * 条件：状态，类型id
     *
     * @param string $typeId
     * @return array
     */
    public static function fetchBannerUnlockLoginByTypeId325($typeId = '')
    {
        $unlocks = BannerUnlockLogin::select(['id', 'unlock_title', 'unlock_subtitle', 'unlock_day', 'succ_unlock_img', 'fail_unlock_img', 'position', 'unlock_pro_num'])
            ->where(['status' => 1, 'type_id' => $typeId])
            ->where('nid', '!=', BannersConstant::BANNER_UNLOCK_LOGIN_NID_UNLOCK_NEW_325)
            ->orderBy('position', 'asc')
            ->get()->toArray();

        return $unlocks ? $unlocks : [];
    }

    /**
     * 根据主键id查询广告解锁连登信息
     *
     * @param string $id
     * @return array
     */
    public static function fetchBannerUnlockLoginById($id = '')
    {
        $unlocks = BannerUnlockLogin::select(['id', 'nid', 'type_id', 'unlock_title', 'unlock_subtitle', 'unlock_day', 'succ_unlock_img', 'fail_unlock_img', 'position', 'cover_img', 'bg_color', 'unlock_pro_num'])
            ->where(['status' => 1, 'id' => $id])
            ->first();

        return $unlocks ? $unlocks->toArray() : [];
    }

    /**
     * 查询下一期解锁连登需求信息
     * 条件：类型，位置
     *
     * @param array $data
     * @return array
     */
    public static function fetchBannerUnlockLoginByPosition($data = [])
    {
        $unlocks = BannerUnlockLogin::select(['id', 'type_id', 'unlock_title', 'unlock_subtitle', 'unlock_pro_num', 'unlock_day', 'succ_unlock_img', 'fail_unlock_img', 'position'])
            ->where(['status' => 1, 'type_id' => $data['type_id'], 'position' => $data['position']])
            ->limit(1)
            ->first();

        return $unlocks ? $unlocks->toArray() : [];
    }

    /**
     * 根据用户最大连登天数，判断下一期展示产品
     *
     * @param array $data
     * @return array
     */
    public static function fetchBannerUnlockLoginByDesc($data = [])
    {
        $unlocks = BannerUnlockLogin::select(['id', 'type_id', 'unlock_title', 'unlock_subtitle', 'unlock_pro_num', 'unlock_day', 'succ_unlock_img', 'fail_unlock_img', 'position'])
            ->where(['status' => 1, 'type_id' => $data['type_id']])
            ->where('unlock_day', '>', $data['login_count'])
            ->orderBy('position', 'asc')
            ->limit(1)
            ->first();

        return $unlocks ? $unlocks->toArray() : [];
    }

    /**
     * 解锁产品总数
     * 条件：类型id，状态
     *
     * @param string $typeId
     * @return int
     */
    public static function fetchBannerUnlockProCountByTypeId($typeId = '')
    {
        $unlockProCount = BannerUnlockLogin::select(['unlock_pro_num'])
            ->where('nid', '!=', BannersConstant::BANNER_UNLOCK_LOGIN_NID_UNLOCK_NEW)
            ->where(['status' => 1, 'type_id' => $typeId])
            ->sum('unlock_pro_num');

        return $unlockProCount ? $unlockProCount : 0;
    }

    /**
     * 根据用户最大连登天数判断可见广告ids
     * 条件：第一版广告nid集合，小于等于用户最大连登天数
     *
     * @param array $params
     * @return array
     */
    public static function fetchBannerUnlockLoginIdsByUserLoginCount($params = [])
    {
        $ids = BannerUnlockLogin::select(['id'])
            ->whereIn('nid', $params['unloSign'])
            ->where('unlock_day', '<=', $params['userUnloCount'])
            ->pluck('id')->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 连登广告点击流水
     *
     * @param array $data
     * @return bool
     */
    public static function createBannerUnlockLoginLog($data = [])
    {
        //连登广告
        $bannerUnlock = $data['bannerUnlock'];
        //渠道
        $deliverys = $data['deliverys'];
        $log = new DataBannerUnlockLoginLog();
        $log->user_id = $data['userId'];
        $log->unlock_login_id = isset($bannerUnlock['id']) ? $bannerUnlock['id'] : '';
        $log->nid = isset($bannerUnlock['nid']) ? $bannerUnlock['nid'] : '';
        $log->unlock_title = isset($bannerUnlock['unlock_title']) ? $bannerUnlock['unlock_title'] : '';
        $log->unlock_day = isset($bannerUnlock['unlock_day']) ? $bannerUnlock['unlock_day'] : '';
        $log->unlock_subtitle = isset($bannerUnlock['unlock_subtitle']) ? $bannerUnlock['unlock_subtitle'] : '';
        $log->position = isset($bannerUnlock['position']) ? $bannerUnlock['position'] : '';
        $log->channel_id = isset($deliverys['id']) ? $deliverys['id'] : '';
        $log->channel_title = isset($deliverys['title']) ? $deliverys['title'] : '';
        $log->channel_nid = isset($deliverys['nid']) ? $deliverys['nid'] : '';
        $log->shadow_nid = isset($data['shadowNid']) ? $data['shadowNid'] : '';
        $log->device_id = isset($data['deviceNum']) ? $data['deviceNum'] : '';
        $log->click_source = isset($data['clickSource']) ? $data['clickSource'] : '';
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * 新用户解锁产品配置信息
     * 条件：状态，类型id
     *
     * @param string $typeId
     * @return array
     */
    public static function fetchBannerUnlockLoginNewUserByTypeId($typeId = 1)
    {
        $unlocks = BannerUnlockLogin::select(['id', 'unlock_pro_num', 'login_pro_num'])
            ->where(['status' => 1,'nid'=>BannersConstant::BANNER_UNLOCK_LOGIN_NID_NEW_USER_UNLOCK_PRO,'type_id' => $typeId])
            ->first()->toArray();

        return $unlocks ? $unlocks : [];
    }
}
