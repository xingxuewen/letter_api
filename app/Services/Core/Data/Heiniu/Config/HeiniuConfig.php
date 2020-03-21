<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-1-18
 * Time: 上午11:02
 */
namespace App\Services\Core\Data\Heiniu\Config;

class HeiniuConfig {
    // url
    const URL = PRODUCTION_ENV ? 'http://www.heiniubao.com/insurance/enhanced' : 'http://47.92.104.74:9099/insurance/enhanced';
    // channel
    const CHANNEL = 'sudaizhijia';
    // subchannel
    const SUBCHANNEL = 'sudaizhijiaapi1';
    // des key
    const DES_KEY = 'a1d3980c';
}