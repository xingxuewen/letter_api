<?php

namespace MyController\UCClient\UCenterService;

use Illuminate\Support\Facades\Config;

if (!defined('UC_API')) {
    $config = config('uc-client');
    defined('UC_CONNECT') or define('UC_CONNECT', $config['connect']);
    defined('UC_DBHOST') or define('UC_DBHOST', $config['dbhost']);
    defined('UC_DBUSER') or define('UC_DBUSER', $config['dbuser']);
    defined('UC_DBPW') or define('UC_DBPW', $config['dbpw']);
    defined('UC_DBNAME') or define('UC_DBNAME', $config['dbname']);
    defined('UC_DBCONNECT') or define('UC_DBCONNECT', 0);
    defined('UC_DBCHARSET') or define('UC_DBCHARSET', $config['dbcharset']);
    defined('UC_DBTABLEPRE') or define('UC_DBTABLEPRE', $config['dbtablepre']);
    defined('UC_KEY') or define('UC_KEY', $config['key']);
    defined('UC_API') or define('UC_API', $config['api']);
    defined('UC_CHARSET') or define('UC_CHARSET', $config['charset']);
    defined('UC_IP') or define('UC_IP', $config['ip']);
    defined('UC_APPID') or define('UC_APPID', $config['appid']);
    defined('UC_PPP') or define('UC_PPP', $config['ppp']);
    include __DIR__ . '/../uc_client/client.php';
}

class UCenterClient
{

    public static function execute($api, $params = array())
    {
        return call_user_func_array($api, $params);
    }

    /**
     *
     * PS: 这是自己附加的方法
     *
     * @param $uid
     * @param string $size
     * @param bool $returnDefault
     * @return string
     */
    public static function avatar($uid, $size = 'small', $returnDefault = FALSE)
    {
        $size = in_array($size, array('big', 'middle', 'small')) ? $size : 'small';

        if ($returnDefault) {
            $url = UC_API . '/images/noavatar_' . $size . '.gif';
        } else {
            $uid = abs(intval($uid));
            $uid = sprintf("%011d", $uid);
            $dir1 = substr($uid, -9, 3);
            $dir2 = substr($uid, -6, 2);
            $dir3 = substr($uid, -4, 2);
            $avatar_file = $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, -2) . "_real_avatar_$size.jpg";
            $url = UC_API . '/data/avatar/' . $avatar_file;
        }
        return $url;
    }

    /**
     * 目前 UCenter的头像尺寸是
     * {big:200, middle:120, small:48}
     * PS: 这是自己附加的方法
     *
     * @param $uid
     * @param bool $returnDefault
     * @return array
     */
    public static function avatars($uid, $returnDefault = FALSE)
    {
        $uid = abs(intval($uid));
        $uid = sprintf("%011d", $uid);
        $dir1 = substr($uid, -9, 3);
        $dir2 = substr($uid, -6, 2);
        $dir3 = substr($uid, -4, 2);
        $avatar_file = $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, -2) . "_real_avatar_";

        $avatars = [];
        foreach (['big', 'middle', 'small'] as $size) {
            if ($returnDefault) {
                $url = UC_API . '/images/noavatar_' . $size . '.gif';
            } else {
                $url = UC_API . '/data/avatar/' . $avatar_file . $size . '.jpg';
            }
            $avatars[$size] = $url;
        }

        return $avatars;
    }
}
