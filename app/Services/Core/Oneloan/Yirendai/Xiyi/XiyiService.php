<?php
namespace App\Services\Core\Oneloan\Yirendai\Xiyi;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use App\Services\Core\Oneloan\Yirendai\Xiyi\XiyiCofig\XiyiConfig;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class XiyiService extends AppService
{
    /**
     * 注册接口
     *
     * @param array $params
     * @param $success
     * @param $fail
     * @return mixed
     */
    public static function register($params = [], callable $success, callable $fail)
    {

        //请求url
        $url = XiyiConfig::URL;
        //获取渠道号
        //$channelNid = DeliveryFactory::fetchDeliveryNId($params['user_id']);

        //请求
        $request = [
            'multipart' => [
                [
                    'name'     => 'sourceCode',
                    'contents' => XiyiConfig::CODE,
                ],
                [
                    'name'     => 'secretKey',
                    'contents' => XiyiConfig::KEY,
                ],
                [
                    'name'     => 'username',
                    'contents' => $params['name'],
                ],
                [
                    'name'     => 'tel',
                    'contents' => $params['mobile'],
                ],
                [
                    'name'     => 'gender',
                    'contents' => $params['sex'] == 1 ? '男' : '女',
                ],
            ],
        ];

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