<?php

namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\Token;
use App\Models\Orm\UserAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TokenController extends Controller
{

    /**
     * 根据 noce_token 获取 access_token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noce2access(Request $request)
    {
        $onceToken = $request->input('token');
        $userInfo = null;

        if (!empty($onceToken)) {
            $tokenObj = new Token();
            $userId = (int) $tokenObj->verify($onceToken);
            //var_dump($onceToken, $userId);exit;
            if ($userId > 0) {
                $userInfo = UserAuth::where('sd_user_id', $userId)->first();
            }
        }

        if (!empty($userInfo)) {
            $data = ['access_token' => $userInfo->accessToken];
            return RestResponseFactory::ok($data);
        } else {
            return RestResponseFactory::unauthorized();
        }
    }


}
