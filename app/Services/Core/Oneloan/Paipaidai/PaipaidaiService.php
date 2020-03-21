<?php

namespace App\Services\Core\Oneloan\Paipaidai;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use App\Services\Core\Oneloan\Paipaidai\Config\PaipaidaiConfig;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * 拍拍贷 —— 接口对接Service
 * Class PaipaidaiService
 * @package App\Services\Core\Data\Paipaidai
 */
class PaipaidaiService extends AppService
{
    /**
     * 拍拍贷 —— 接口对接Service
     *
     * @param $datas
     * @param $success
     * @param $fail
     */
    public static function spread($datas, $success, $fail)
    {
        $token = PaipaidaiConfig::TOKEN;
        // 请求参数
        $request = [
            'form_params' => [
                'ChannelId' => PaipaidaiConfig::CHANNEL,               // CHANNEL
                'SourceId'  => PaipaidaiConfig::SOURCE,                // SOURCE
                'token'     => $token,                                 // TOKEN
                'sign'      => PaipaidaiConfig::getSign($token, $datas['mobile']),// 签名
                'phone'       => $datas['mobile'],                     // 手机号
                'userName'    => $datas['name'],                   // 姓名
                'shenfenzh'   => $datas['certificate_no'],             // 身份证号
                'applyLoanAmount' => $datas['money'],                  //贷款金额
                'applyLoanMonth'  => 7,                                // 贷款期限, 约定默认7
                'daikuanyt'       => '日常消费',                        // 贷款用途
                'loanType'        => '个人贷款',                        // 贷款类型, 非必填约定默认"个人贷款"
                'info'            => PaipaidaiConfig::getInfo($datas)
            ]
        ];
        // 获取url
        $url = PaipaidaiConfig::URL;

        $promise = HttpClient::i()->requestAsync('POST', $url, $request);

        $promise->then(
            function (ResponseInterface $res) use($success) {
                $result = $res->getBody()->getContents();
                $success(json_decode($result, true));
            },
            function (RequestException $e) use($fail) {
                $fail($e);
            }
        );

        $promise->wait();
    }
}