<?php

namespace App\Services\Core\Oneloan\Rongdai\Rongdai;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\UserSpreadFactory;
use App\Services\AppService;
use App\Services\Core\Oneloan\Rongdai\Rongdai\Config\Config;
use App\Services\Core\Oneloan\Rongdai\Rongdai\Util\RsaUtil;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * 融贷网
 *
 * Class RongdaiService
 * @package App\Services\Core\Platform\Rongdai\Rongdai
 */
class RongdaiService extends AppService
{
    /**
     * 融贷网
     *
     * @param array $params
     * @param callable $success
     * @param callable $fail
     */
    public static function spread($params = [], callable $success, callable $fail)
    {
        //获取城市编码code
        $cityCode = UserSpreadFactory::fetchUserSpreadAreasByTypeIdAndCityName($params);
        $params['city_code'] = $cityCode ? $cityCode['city_code'] : '';
        if($params['money']>=10000 and $params['money']<30000){
            $params['money']=30000;
        }
        //地址
        $url = Config::URL;

        //请求参数
        $data = [
            'username' => $params['name'],
            'money' => intval($params['money']),
            'zone_id' => $params['city_code'],
            'month' => 12,
            'mobile' => $params['mobile'],
            'age' => $params['age'],
            'salary_bank_public' => RsaUtil::formatSalary($params['salary']), //月收入
            'salary_bank_private' => RsaUtil::formatSalaryExtend($params['salary_extend']), //工资发放形式
            'is_fund' => $params['accumulation_fund'] == '000' ? 2 : 1, //公积金类型 1有 2无
            'is_security' => $params['social_security'] == 0 ? 2 : 1, //社保类型 1有 2无
            'house_type' => $params['house_info'] == '000' ? 1 : 2, //房产类型 1无 2有,未抵押
            'car_type' => $params['car_info'] == '000' ? 1 : 2, //车产类型 1无 2有,未抵押
            'credit_card' => $params['has_creditcard'] == 0 ? 2 : 1, //是否有信用卡 1有 2无
            'is_buy_insurance' => $params['has_insurance'] == 0 ? 2 : 1, //是否有保单 1无 2有
            'mobile_hash' => md5($params['mobile']),
        ];

        //logInfo('request',['data'=>$data]);

        //加密
        $filed = RsaUtil::encrypt(json_encode($data));

        //请求数据
        $request = [
            'json' => [
                'channel_id' => Config::CHANNEL_ID,
                'biaoshi' => Config::BIAOSHI_ID,
                'field' => $filed,
            ],
        ];

        //请求接口
        $promise = HttpClient::i()->requestAsync('POST', $url, $request);

        $promise->then(
            function (ResponseInterface $res) use ($success) {
                $result = $res->getBody()->getContents();
                $success(json_decode($result, true));
            },
            function (RequestException $e) use ($fail) {
                $fail($e);
            }
        );

        $promise->wait();
    }
}
