<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Services\Core\Platform\Jietiao\Suijiesuihua\SuijiesuihuaService;
use App\Services\Core\Promotion\Sudai\SudaiService;

/**
 * 测试
 *
 * Class TestController
 * @package App\Http\Controllers\Promotion
 */
class TestController extends Controller
{
    public function fetchSudaiUrl()
    {
        $data['mobile'] = '13522960570';
        //加密处理
        $url = SudaiService::fetchSudaiUrl($data);

        $url = json_decode($url,true);
        dd($url);

//        $datas['user']['mobile'] = '13522960570';
//        $datas['user']['real_name'] = 'asdsa';
//        $datas['user']['idcard'] = '13522960570';
//        $datas['page'] = '';
//
//        $url = SuijiesuihuaService::fetchSuijieUrl($datas);

    }
}