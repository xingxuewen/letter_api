<?php

use Illuminate\Support\Facades\Log;

if (! function_exists('logInfo')) {
    function logInfo($msg, $data = [])
    {
        $data = (array) $data;
        Log::info($msg, $data);
    }
}

if (! function_exists('logError')) {
    function logError($msg, $data = [])
    {
        $data = (array) $data;
        Log::error($msg, $data);
    }
}

if (! function_exists('logWarning')) {
    function logWarning($msg, $data = [])
    {
        $data = (array) $data;
        Log::warning($msg, $data);
    }
}

if (! function_exists('logDebug')) {
    function logDebug($msg, $data = [])
    {
        $data = (array) $data;
        Log::debug($msg, $data);
    }
}

if (! function_exists('getTraceId')) {
    function getTraceId()
    {
        static $traceId = null;

        if ($traceId === null) {
            if (!empty($_REQUEST['traceId'])) {
                $traceId = $_REQUEST['traceId'];
            } else if (!empty($_GET['traceId'])) {
                $traceId = $_GET['traceId'];
            } else if (!empty($_POST['traceId'])) {
                $traceId = $_POST['traceId'];
            } else if (!empty($_SERVER['traceId'])) {
                $traceId = $_SERVER['traceId'];
            } else {
                $traceId = hash('md5', uniqid('', true));
            }
        }

        return $traceId;
    }
}

if (! function_exists('getRequestId')) {
    function getRequestId()
    {
        static $requestId = null;

        if ($requestId === null) {
            $requestId = substr(hash('md5', uniqid('', true)), 0, 10);
        }

        return $requestId;
    }
}

if (! function_exists('isNewVersion')) {
    function isNewVersion()
    {

        return true;
    }
}

if (!function_exists('getOsType')) {
    function getOsType()
    {
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);

        //分别进行判断
        if(strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            return 1;
        }

        if(strpos($agent, 'android')) {
            return 2;
        }

        return 3;
    }
}

if (!function_exists('arrayMerge')) {
    /**
     * 原生array_merge，当有一个 $array 为空时，会重置结果数组下标
     *
     * @param $array1
     * @param $array2
     * @return array
     */
    function arrayMerge($array1, $array2)
    {
        if (empty($array1)) {
            return $array2;
        }

        if (empty($array2)) {
            return $array1;
        }

        if (empty($array1) && empty($array2)) {
            return [];
        }

        $res = $array1;

        foreach ($array2 as $key => $val) {
            $res[$key] = $val;
        }

        return $res;
    }
}