<?php

namespace App\Services\Core\Platform;

use App\Models\Factory\OauthFactory;
use App\Services\AppService;
use App\Services\Core\Platform\Daishangqian\DaishangqianService;
use App\Services\Core\Platform\Fangsiling\FangsilingService;
use App\Services\Core\Platform\Faxindai\FaxindaiService;
use App\Services\Core\Platform\Jietiao\Suijiesuihua\SuijiesuihuaService;
use App\Services\Core\Platform\Jimu\JimuService;
use App\Services\Core\Platform\Jindoukuaidai\JindoukuaidaiService;
use App\Services\Core\Platform\Jiufuqianbao\Jiufudingdang\JiufudingdangService;
use App\Services\Core\Platform\JsXianjinxia\Xianjinxia\XianjinxiaService;
use App\Services\Core\Platform\Doubei\Doubei\DoubeiService;
use App\Services\Core\Platform\Kami\Kami\KamiService;
use App\Services\Core\Platform\Danhuahua\Danhuahua\DanhuahuaService;
use App\Services\Core\Platform\Mobp2p\Mobp2pService;
use App\Services\Core\Platform\Quhuafenqi\QuhuafenqiService;
use App\Services\Core\Platform\Renxinyong\Renxinyong\RenxinyongService;
use App\Services\Core\Platform\Xyqb\XyqbService;
use App\Services\Core\Platform\Xinerfu\Xianjindai\XianjindaiService;
use App\Services\Core\Platform\Yirendai\Yirendai\YirendaiService;
use App\Services\Core\Platform\Jiufuwanka\Xianjin\JiufuwankaxianjinService;
use App\Services\Core\Platform\Rong360\Yuanzidai\YuanzidaiService;
use App\Services\Core\Platform\Shuixiang\Shuixiangfenqi\ShuixiangfenqiService;
use App\Services\Core\Platform\Jielebao\JielebaoService;
use DB;

class PlatformService extends AppService
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
     * @param $params
     * @return mixed
     * 获取对接url
     */
    public function toPlatformService($datas)
    {
        //默认url
        $pageData['url'] = $datas['page'];

        //类型id
        $typeNid = $datas['product']['type_nid'];

        //产品id
        $productId = $datas['productId'];

        //如果不要求产品是上线状态
        if (isset($datas['is_nothing']) && $datas['is_nothing'] == 1) {
            $channelStatus = 1; //1开
            $channelStatus = OauthFactory::checkProductChannelStatusNothing($productId, $channelStatus);
        } else {
            $channelStatus = 1; //1开
            $channelStatus = OauthFactory::checkProductChannelStatus($productId, $channelStatus);
        }

        if (!$channelStatus || empty($typeNid)) {
            return $pageData;
        }

        switch ($typeNid) {
            case 'DM':    //读秒/积木
                $service = new JimuService();
                $res = $service->fetchInfo($datas['userId']);
                if (!empty($res['url'])) {
                    $pageData['apply_url'] = $res['url'];
                }
                break;
            case 'FXD':     //发薪贷/应急贷
                $pageData = FaxindaiService::fetchFaxindaiUrl($datas);
                break;
            case 'XYQB':    //量化派/信用钱包
                $pageData = XyqbService::fetchQuantgroupUrl($datas);
                break;
            case 'SJD':     //手机贷/简单易贷
                $pageData = Mobp2pService::fetchMobp2pPage($datas);
                break;
            case 'DSQ':     //贷上钱/贷上钱
                $pageData = DaishangqianService::fetchDaishangqianUrl($datas);
                break;
            case 'JFDDD':   //玖富钱包/玖富叮当贷
                $pageData = JiufudingdangService::fetchJiufudingdangUrl($datas);
                break;
            case 'XJD':     //信而富/现金贷
                $pageData = XianjindaiService::fetchXinerfuUrl($datas);
                break;
            case 'XJX':     //极速现金侠/现金侠
                $pageData = XianjinxiaService::fetchXianjinxiaUrl($datas);
                break;
            case 'YRD':     //宜人贷
                $pageData = YirendaiService::fetchYirendaiUrl($datas);
                break;
            case 'JFWK-XJ': //玖富万卡/玖富万卡现金
                $pageData = JiufuwankaxianjinService::fetchJiufuwankaUrl($datas);
                break;
            case 'JDKD':    //筋斗快贷
                $pageData = JindoukuaidaiService::fetchJindoukuaidaiUrl($datas);
                break;
            case 'RXY':     //任信用
                $pageData = RenxinyongService::fetchRenxinyongUrl($datas);
                break;
            case 'DB':      //抖贝
                $pageData = DoubeiService::fetchDoubeiUrl($datas);
                break;
            case 'SJSH':    //借条-随借随花
                $pageData = SuijiesuihuaService::fetchSuijieUrl($datas);
                break;
            case 'YZD':     //原子贷
                $pageData = YuanzidaiService::fetchYuanzidaiUrl($datas);
                break;
            case 'KM':      //卡秘
                $pageData = KamiService::fetchKamiUrl($datas);
                break;
            case 'DHHH':    //蛋花花
                $pageData = DanhuahuaService::fetchDanhuahuaUrl($datas);
                break;
            case 'SXJDH':   //水象
                $pageData = ShuixiangfenqiService::fetchShuixiangfenqiUrl($datas);
                break;
            case 'JLB':     //借乐宝
                $pageData = JielebaoService::fetchJielebaoUrl($datas);
                break;
            case 'SJD-XJFQ'://趣花分期
                $pageData = QuhuafenqiService::fetchQuhuafenqiUrl($datas);
                break;
            case 'FSL':     //房司令
                $pageData = FangsilingService::fetchFangsilingUrl($datas);
                break;
            default:
                $pageData['apply_url'] = $datas['page'];
                break;
        }

        $pageData['url'] = isset($pageData['apply_url']) ? $pageData['apply_url'] : $datas['page'];
        return $pageData;
    }

    /**
     * @return array
     * 对接平台地址
     */
    public function getCooperate()
    {
        return [
            //历史地址  'url' => 'http://116.236.225.158:8020/fxd-h5/page/thirdIndex.html',
            'faxindai' => [
                'platform_name' => '发薪贷',
            ],
            'xinyongqianbao' => [
                'platform_name' => '信用钱包',
                //'url' => 'http://61.50.125.14:9001/app/login'   //测试环境
                'url' => 'http://auth.xyqb.com/app/login'   //线上环境
            ],
            'shoujidai' => [
                'platform_name' => '手机贷',
                'url' => 'http://m.mobp2p.com/union/login/' //生产环境
                //'url' => 'http://116.228.32.182:7070/wap/union/login' //测试环境
            ],
            'daishangqian' => [
                'platform_name' => '贷上钱',
                'url' => 'https://api.daishangqian.com/asset/third/register' //生产环境
                //'url' => 'http://paydayloan.fond.io/asset/third/register' //测试环境
            ],
        ];
    }
}