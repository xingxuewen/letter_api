<?php

namespace App\Services\Core\Validator\FaceId\Megvii;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Services\Core\Validator\ValidatorService;

/**
 * Face++升级接口
 *
 * Class MegviiService
 * @package App\Services\Core\Validator\FaceId
 */
class MegviiService extends ValidatorService
{

    /**
     * 检测和识别中华人民共和国第二代身份证
     * 身份证OCR识别API的第一个发行版本。由于V2.0.0以上的版本并不兼容老版本API
     * V2.0.0
     * @param $image
     * @return mixed
     */
    public static function fetchOcriIdcardToInfo($image)
    {
        $url = ValidatorService::FACEID_API_URL_UPGRADE . '/faceid/v3/ocridcard';
        $apiKey = ValidatorService::getFaceidAppKey();
        $apiSecret = ValidatorService::getFaceidAppSecret();

        $request = [
            'multipart' => [
                [
                    'name' => 'image',
                    'contents' => fopen($image, 'r'),
                ],
                [
                    'name' => 'api_key',
                    'contents' => $apiKey,
                ],
                [
                    'name' => 'api_secret',
                    'contents' => $apiSecret,
                ],
                // 是否返回身份证照片合法性检查结果 “1”：返回； “0”：不返回。
                [
                    'name' => 'legality',
                    'contents' => 1,
                ],
            ],
        ];
        //请求face++
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);
        return $res;
    }

    /**
     * 此接口用于将FaceID MegLiveStill SDK 所获得的数据进行上传，
     * 并获取活体验证、人脸比对、攻击防范等结果信息
     *
     * @param array $params
     * @return mixed
     */
    public static function verify($params = [])
    {
        //请求地址
        $url = ValidatorService::FACEID_API_URL_UPGRADE . 'faceid/v3/sdk/verify';
        //App-鉴权说明
        $sign = self::getAppSign();
        //通过”App-GetBizToken“ API接口获取到的biz_token
        $bizToken = self::getAppBizToken($params);

        $request = [
            'multipart' => [
                //调用此API客户的签名
                [
                    'name' => 'sign',
                    'contents' => $sign,
                ],
                //签名算法版本，请传递：hmac_sha1
                [
                    'name' => 'sign_version',
                    'contents' => 'hmac_sha1',
                ],
                //通过”App-GetBizToken“ API接口获取到的biz_token
                [
                    'name' => 'biz_token',
                    'contents' => $bizToken,
                ],
                //由FaceID MegLiveStill SDK 3.0及以上版本生成的数据，内容包括活体验证过程中的数据，和采集到的人脸数据。
                //请不要对数据包做任何调整，直接提交接口即可。
                [
                    'name' => 'meglive_data',
                    'contents' => fopen($params['meglive_data'], 'r'),
                ],
            ],
        ];


        //请求face++
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);
        return $res;
    }

    /**
     * 在调用App-GetBizToken以及App-Verify等API接口时，为了保障密钥（api_secret）不被泄露，因此，这里引入签名机制来确保服务器之间的通信安全。
     *
     * @param array $params
     * @return string
     */
    public static function getAppSign($params = [])
    {
        $apiKey = ValidatorService::getFaceidAppKey();
        $apiSecret = ValidatorService::getFaceidAppSecret();
        $rdm = rand();
        $current_time = time();
        $expired = 3600;
        $expired_time = $current_time + $expired;

        //参数
        $signDatas = [
            'a' => $apiKey,
            'b' => $expired_time,
            'c' => $current_time,
            'd' => $rdm,
        ];
        //拼接成字符串
        $srcStr = '';
        foreach ($signDatas as $key => $val) {
            $srcStr .= $key . '=' . $val . '&';
        }
        $srcStr = rtrim($srcStr, '&');

        //加密
        $srcStr = sprintf($srcStr, $apiKey, $expired_time, $current_time, $rdm);
        $sign = base64_encode(hash_hmac('SHA1', $srcStr, $apiSecret, true) . $srcStr);
        return $sign;
    }

    /**
     * 此接口用于配置人脸比对的身份核实功能，支持有源比对（调用者提供姓名、身份证号、和待核实人脸图）和无源比对（直接比对待核实人脸图和参照人脸图）。客户通过服务器将本次活体相关的配置传到FaceID服务器，在验证无误后，返回本次业务的biz_token，用FaceID MegLiveStill SDK的初始化。
     *
     *
     * @param array $params
     * @return mixed
     */
    public static function getAppBizToken($params = [])
    {
        //请求地址
        $url = ValidatorService::FACEID_API_URL_UPGRADE . '/faceid/v3/sdk/get_biz_token';
        //App-鉴权说明
        $sign = self::getAppSign();

        $request = [
            'multipart' => [
                //调用此API客户的签名
                [
                    'name' => 'sign',
                    'contents' => $sign,
                ],
                //签名算法版本，请传递：hmac_sha1
                [
                    'name' => 'sign_version',
                    'contents' => 'hmac_sha1',
                ],
                //表示返回数据的详细程度，取值如下： 0：默认值，仅返回结论 1：返回结论与摘要信息
                [
                    'name' => 'verbose',
                    'contents' => '1',
                ],
                //活体验证流程的选择，目前仅取以下值：meglive：动作活体  still：静默活体
                [
                    'name' => 'liveness_type',
                    'contents' => MegviiConfig::ALIVE_STILL,
                ],
                //无源对比0，有缘对比1
                [
                    'name' => 'comparison_type',
                    'contents' => '0',
                ],
                //无缘对比 uuid
                [
                    'name' => 'uuid',
                    'contents' => $params['userId'] . '',
                ],
                //无缘对比 图片1 身份证正面照
                [
                    'name' => 'image_ref1',
                    'contents' => fopen($params['card_front'], 'r'),

                ],
                //无缘对比 图片2 身份证大头照
                [
                    'name' => 'image_ref2',
                    'contents' => fopen($params['card_photo'], 'r'),
                ],
            ],
        ];


        //请求face++
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);
        return $res;

    }
}