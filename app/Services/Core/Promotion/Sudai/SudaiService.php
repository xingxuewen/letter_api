<?php

namespace App\Services\Core\Promotion\Sudai;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use App\Services\Core\Promotion\Sudai\Util\RsaUtil;

/**
 * 速贷联登对接
 *
 * Class SudaiService
 * @package App\Services\Core\Promotion\Sudai
 */
class SudaiService extends AppService
{

    public static function fetchSudaiUrl($datas = [])
    {
        //对接参数
        $params = [
            'mobile' => $datas['mobile'], // 手机号
            'partnerId' => 'sudaizhijia', // 渠道号 [对方]
        ];

        //转化json
        $jsonParams = json_encode($params);
        //公钥加密
        $rsaParams = RsaUtil::i()->rsaEncrypt($jsonParams);

        $request = [
            'form_params' => [
                'encrypt_data' => $rsaParams,
            ],
        ];

        $url = 'http://dev.api.sudaizhijia.com/promotion/auth/login';
        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        dd($result);

        return json_decode($result, true);
    }

    /**
     * 解密
     *
     * @param array $params
     * @return string
     */
    public static function undoData($params = [])
    {
        return RsaUtil::i()->privateDecrypt($params);
    }
}