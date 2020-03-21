<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{

    /**
     * Instantiate a new Controller instance.
     */
    public function __construct()
    {
        date_default_timezone_set('Asia/Shanghai'); //时区配置

        $request = Request::getFacadeRoot();
        if (!empty($request) && method_exists($request, 'user')) {
            logInfo('userinfo', ['info' => $request->user()]);
        }
    }

    public function getToken($request)
    {
        return $request->input('token') ?: $request->header('X-Token');
    }

    public function getUserId($request)
    {
        return $request->user()->sd_user_id ?: null;
    }

}
