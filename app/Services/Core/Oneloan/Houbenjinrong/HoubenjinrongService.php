<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-3-13
 * Time: 下午7:01
 */
namespace App\Services\Core\Oneloan\Houbenjinrong;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Services\Core\Oneloan\Houbenjinrong\HoubenjinrongConfig\HoubenjinrongConfig;
use Illuminate\Support\Facades\Log;

class HoubenjinrongService extends AppService
{
    /*
     * 推送
     *
     * @param $params
     * @return mixed
     */
    public static function push($params, callable $success, callable $fail)
    {
        $data = [
            'supplierCode' => HoubenjinrongConfig::SUPPLIER_ID,
            'importIndex' => date("YmdHis").rand(100000,999999),
            'importList' => [
                [
                    'name' => $params['name'],
                    'phone' => $params['mobile'],
                    'birthday' => $params['birthday'],
                    'cityCode' => $params['cityCode'],
//                    'cardType' => '01',
//                    'cardNo' => $params['cardNo'],
                    'professionType' => $params['professionType'],
                    'incomeRange' => $params['incomeRange'],
                    'incomeType' => $params['incomeType'],
                    'isWelfare' => $params['isWelfare'],
                    'isHousingFund' => $params['isHousingFund'],
                    'isHasCar' => $params['isHasCar'],
                    'isCarLoan' => $params['isCarLoan'],
                    'isHasHouse' => $params['isHasHouse'],
                    'isInsurance' => $params['isInsurance'],
                    'custSex' => $params['custSex'],
                    'isAtom' => $params['isAtom'],
               ]
            ]
        ];

        $json = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json = HoubenjinrongConfig::encrypt($json);
        $content = ['sendData' => $json];
        $contentStr = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $contentEncode = base64_encode($contentStr);
        $url = HoubenjinrongConfig::TELE_URL .'/hb_telesale/flex/msg/ImportList0utService.action';
        $bUrl = base64_encode($url);
        $servelUrl = HoubenjinrongConfig::FRONT_URL .'/front/HttpForwardProcessor';
        $request = [ //form_params
            'form_params' => [
                'params' => $contentEncode,
                'url' => $bUrl,
                'method' => 'post',
                'posttype' => 'HTTPCLIENT',
                'charcode' => 'utf-8',
            ],
        ];
        $promise = HttpClient::i()->requestAsync('POST', $servelUrl, $request);

        $promise->then(
            function (ResponseInterface $res) use($success) {
                $result = $res->getBody()->getContents();
                $res = json_decode(base64_decode($result), true);
                if(empty($res) || !isset($res["returnData"]))
                {
                    return [];
                }
                //解密
                $rest = HoubenjinrongConfig::decrypt($res["returnData"]);
                $success(json_decode($rest, true));
            },
            function (RequestException $e) use($fail) {
                $fail($e);
            }
        );

        $promise->wait();

    }
}