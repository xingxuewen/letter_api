<?php

namespace App\Services\Core\Oneloan;

use App\Constants\SpreadNidConstant;
use App\Events\Oneloan\Partner\UserChunyuEvent;
use App\Events\Oneloan\Partner\UserDazhiEvent;
use App\Events\Oneloan\Partner\UserDongfangEvent;
use App\Events\Oneloan\Partner\UserFangjinsuoEvent;
use App\Events\Oneloan\Partner\UserFinanceEvent;
use App\Events\Oneloan\Partner\UserGongyinyingEvent;
use App\Events\Oneloan\Partner\UserHengchangEvent;
use App\Events\Oneloan\Partner\UserHengyiEvent;
use App\Events\Oneloan\Partner\UserHoubenEvent;
use App\Events\Oneloan\Partner\UserHougedaiEvent;
use App\Events\Oneloan\Partner\UserLoanEvent;
use App\Events\Oneloan\Partner\UserMiaodaiEvent;
use App\Events\Oneloan\Partner\UserMiaolaEvent;
use App\Events\Oneloan\Partner\UserNewLoanEvent;
use App\Events\Oneloan\Partner\UserNiwodaiEvent;
use App\Events\Oneloan\Partner\UserOxygendaiEvent;
use App\Events\Oneloan\Partner\UserPaipaidaiEvent;
use App\Events\Oneloan\Partner\UserRenxinyongEvent;
use App\Events\Oneloan\Partner\UserRongshidaiEvent;
use App\Events\Oneloan\Partner\UserXiaoxiaoEvent;
use App\Events\Oneloan\Partner\UserYouliEvent;
use App\Events\Oneloan\Partner\UserYoulinewEvent;
use App\Events\Oneloan\Partner\UserZhijiechedaiEvent;
use App\Events\Oneloan\Partner\UserZhongtengxinEvent;
use App\Events\Oneloan\Partner\UserInsuranceEvent;
use App\Events\Oneloan\Partner\UserJibaodaiEvent;
use App\Events\Oneloan\Partner\UserXiyiEvent;
use App\Events\Oneloan\Partner\UserCainiaoEvent;
use App\Events\Oneloan\Partner\UserZhanyewangEvent;
use App\Events\Oneloan\Partner\UserRongdaiEvent;
use App\Events\Oneloan\Partner\UserYiyangEvent;
use App\Events\Oneloan\Partner\UserJiajiarongEvent;
use App\Services\AppService;
use DB;

/**
 * 一键贷
 * Class OneloanService
 * @package App\Services\Core\Oneloan
 */
class OneloanService extends AppService
{

    public static $services;

    public static function i()
    {

        if (!(self::$services instanceof static)) {
            self::$services = new static();
        }

        return self::$services;
    }

    /**
     * 根据参数去推送产品
     * @param array $params
     * @return mixed
     */
    public function to($params = [])
    {
        $typeNid = $params['type_nid'];
        //logInfo('推送type_nid确认',['data'=>$typeNid]);
        switch ($typeNid) {
            case SpreadNidConstant::SPREAD_HEINIU_NID:
                // 触发赠险事件
                event(new UserInsuranceEvent($params));
                break;
            case SpreadNidConstant::SPREAD_DONGFANG_NID:
                // 东方事件
                event(new UserDongfangEvent($params));
                break;
            case SpreadNidConstant::SPREAD_XIAOXIAO_NID:
                // 小小金融事件
                event(new UserFinanceEvent($params));
                break;
            case SpreadNidConstant::SPREAD_HENGCHANG_NID:
                // 恒昌事件
                event(new UserHengchangEvent($params));
                break;
            case SpreadNidConstant::SPREAD_HOUBEN_NID:
                // 厚本事件
                event(new UserHoubenEvent($params));
                break;
            case SpreadNidConstant::SPREAD_ZHUDAIWANG_NID:
                // 助贷网事件
                event(new UserLoanEvent($params));
                break;
            case SpreadNidConstant::SPREAD_XINYIDAI_NID:
                // 新一贷事件
                event(new UserNewLoanEvent($params));
                break;
            case SpreadNidConstant::SPREAD_OXYGENDAI_NID:
                // 氧气贷事件
                event(new UserOxygendaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_PAIPAIDAI_NID:
                // 拍拍贷事件
                event(new UserPaipaidaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_XIAOXIAO_SECOND_NID:
                // 小小金融2事件
                event(new UserXiaoxiaoEvent($params));
                break;
            case SpreadNidConstant::SPREAD_YOULI_NID:
                // 有利保险事件
                event(new UserYouliEvent($params));
                break;
            case SpreadNidConstant::SPREAD_ZHONGTENGXIN_NID:
                // 中腾信事件
                event(new UserZhongtengxinEvent($params));
                break;
            case SpreadNidConstant::SPREAD_MIAODAI_NID:
                // 秒贷事件
                event(new UserMiaodaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_GONGYINYING_NID:
                // 工银英事件
                event(new UserGongyinyingEvent($params));
                break;
            case SpreadNidConstant::SPREAD_RONGSHIDAI_NID:
                // 融时代事件
                event(new UserRongshidaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_NIWODAI_NID;
                //你我贷
                event(new UserNiwodaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_MIAOLA_NID;
                //你我貸-秒啦事件
                event(new UserMiaolaEvent($params));
                break;
            case SpreadNidConstant::SPREAD_HENGYIDAI_NID;
                //恒昌 - 恒易贷
                event(new UserHengyiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_HOUGEDAI_NID;
                //猴哥贷
                event(new UserHougedaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_CHUNYU_NID;
                //春雨贷
                event(new UserChunyuEvent($params));
                break;
            case SpreadNidConstant::SPREAD_RENXINYONG_NID;
                //任信用
                event(new UserRenxinyongEvent($params));
                break;
            case SpreadNidConstant::SPREAD_ZHIJIECHEDAI_NID;
                //智借车贷事件
                event(new UserZhijiechedaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_FANGJINSUO_NID;
                //智借车贷事件
                event(new UserFangjinsuoEvent($params));
                break;
            case SpreadNidConstant::SPREAD_XIYI_NID;
                //西伊事件
                event(new UserXiyiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_CAINIAO_NID;
                //财鸟事件
                event(new UserCainiaoEvent($params));
            case SpreadNidConstant::SPREAD_JIBAODAI_NID;
                //吉宝贷事件
                event(new UserJibaodaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_YOULINEW_NID;
                //有利2事件
                event(new UserYoulinewEvent($params));
                break;
            case SpreadNidConstant::SPREAD_ZHANYEWANG_NID;
                //展业王
                event(new UserZhanyewangEvent($params));
                break;
            case SpreadNidConstant::SPREAD_DAZHI_NID;
                //大智金服
                event(new UserDazhiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_RONGDAI_NID;
                //融贷
                event(new UserRongdaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_YIYANG_NID;
                //融贷
                event(new UserYiyangEvent($params));
                break;
            case SpreadNidConstant::SPREAD_JIAJIARONG_NID;
                //佳佳融
                event(new UserJiajiarongEvent($params));
                break;
            default:
        }

        return true;
    }

}
