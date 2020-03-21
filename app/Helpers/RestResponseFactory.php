<?php

namespace App\Helpers;

use App\Helpers\RestResponse;

/**
 * @author zhaoqiying
 */
class RestResponseFactory
{

    //200
    public static function ok($payload = null, $message = null, $errorCode = 0, $errorMessage = '')
    {
        return self::toJson(new RestResponse($payload, 200, $message, $errorCode, $errorMessage), 200);
    }

    //201
    public static function created($payload = null, $message = null, $errorCode = 0, $errorMessage = '')
    {
        return self::toJson(new RestResponse($payload, 201, $message, $errorCode, $errorMessage), 201);
    }

    //302
    public static function redirect($payload = null, $message = null, $errorCode = 0, $errorMessage = '')
    {
        return self::toJson(new RestResponse($payload, 302, $message, $errorCode, $errorMessage), 302);
    }

    //400
    public static function badrequest($message = null, $errorCode = 1, $errorMessage = '')
    {
        return self::toJson(new RestResponse(null, 400, $message, $errorCode, $errorMessage), 400);
    }

    //401
    public static function unauthorized($message = null, $errorCode = 1, $errorMessage = '')
    {
        return self::toJson(new RestResponse(null, 401, $message, $errorCode, $errorMessage), 401);
    }

    //403
    public static function forbidden($message = null, $errorCode = 1, $errorMessage = '')
    {
        return self::toJson(new RestResponse(null, 403, $message, $errorCode, $errorMessage), 403);
    }

    //404
    public static function notfound($message = null, $errorCode = 1, $errorMessage = '')
    {
        return self::toJson(new RestResponse(null, 404, $message, $errorCode, $errorMessage), 404);
    }

    //500
    public static function error($message = null, $errorCode = 1, $errorMessage = '')
    {
        return self::toJson(new RestResponse(null, 500, $message, $errorCode, $errorMessage), 500);
    }

    // Any
    public static function any($payload = null, $code = 200, $message = null, $errorCode = 0, $errorMessage = '')
    {
        return self::toJson(new RestResponse($payload, $code, $message, $errorCode, $errorMessage), $code);
    }

    // cover unicode to utf8
    public static function toJson($payload = null, $code = 200)
    {
        return response()->json($payload, $code, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
