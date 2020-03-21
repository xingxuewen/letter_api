<?php

namespace App\Services\Core\Oneloan\Zhongtengxin;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Services\Core\Oneloan\Zhongtengxin\Config\ZhongtengxinConfig;

/**
 * 中腾信对接
 * Class ZhongtengxinService
 * @package App\Services\Core\Platform\Zhongtengxin
 */
class ZhongtengxinService extends AppService
{
    /**
     * 中腾信推广
     * @param $params
     * @param $success
     * @param $fail
     * @return mixed
     */
    public static function spread($params = [], callable $success, callable $fail)
    {
        //地址渠道channel ?callback=?&channel=
        $vargs = http_build_query([
            'channel' => ZhongtengxinConfig::CHANNEL,
        ]);

        //参数处理  北京市 去除 '市'
        $params['city'] = mb_strpos($params['city'], '市') ? rtrim($params['city'], '市') : $params['city'];

        //请求参数
        $request = [
            'form_params' => [
                'tokenId' => $params['user_id'] . '',   //保证业务唯一
                'name' => $params['name'],  //用户名
                'telephone' => $params['mobile'], //电话
                'city' => $params['city'],  //城市
                'vocation' => ZhongtengxinConfig::getOccupation($params['occupation']), //职业身份
                'income' => ZhongtengxinConfig::getSalary($params['salary']), //收入
                'hasHousingFund' => ZhongtengxinConfig::getHousingFund($params), //是否有社保或公积金
            ],
        ];
        //请求地址
        $url = ZhongtengxinConfig::URL . $vargs;

        $promise = HttpClient::i()->requestAsync('POST', $url, $request);

        $promise->then(
            function (ResponseInterface $res) use($success) {
                $result = $res->getBody()->getContents();
                //返回空值
                if (empty($result)) {
                    return [];
                }
                //返回格式 ?({"result":"非白名单请求,非法"})，进行处理
                $result = mb_substr($result, 2);
                $result = rtrim($result, ')');
                $success(json_decode($result, true));
            },
            function (RequestException $e) use($fail) {
                $fail($e);
            }
        );

        $promise->wait();


    }
}