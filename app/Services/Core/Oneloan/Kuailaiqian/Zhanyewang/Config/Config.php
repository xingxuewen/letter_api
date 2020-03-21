<?php

namespace  App\Services\Core\Oneloan\Kuailaiqian\Zhanyewang\Config;

use App\Models\Factory\UserSpreadFactory;
use App\Helpers\Utils;
/**
 *
 * 环境配置
 *
 * Class Config
 * @package App\Services\Core\Platform\Jibaodai\Jibaodai\Config
 */
class Config
{
    //测试地址
    const TEST_URL = 'http://www.klqian.com/apiforzywsdzj/zywapiforone.html';
    //正式地址
    const FORMAL_URL = 'http://www.klqian.com/apiforzywsdzj/apiforfirst.html';

    //调用地址
    const URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;

    //渠道号
    const CHANNEL_NO = PRODUCTION_ENV ? '2975F796AFC54697A903FD292DA4B475' : '2975F796AFC54697A903FD292DA4B475';

    //明文数据推送地址
    const TEST_PUSH_URL='http://www.klqian.com/apiforzywsdzj/zywapifortwo.html';
    //明文数据正式推送地址
    const FORMAL_PUSH_URL = 'http://www.klqian.com/apiforzywsdzj/apiforsecond.html';

    //调用地址
    const PUSH_URL = PRODUCTION_ENV ? self::FORMAL_PUSH_URL : self::TEST_PUSH_URL;
    /**
     * 1、无房产 2、有房产，还贷中 3、有房产，无房贷 4、不确定
     * 000无房, 001有房贷, 002无房贷
     * @param $city
     * @return array
     */
    public static function getHouse($house_info=''){
        switch ($house_info){
            case '000':
                $res=1;
                break;
            case '001':
                $res=2;
                break;
            case '002':
                $res=3;
                break;
            default:
                $res=4;
                break;
        }
        return $res;
    }

    /**
     * 1、无汽车 2、有汽车，还贷中 3、有汽车，无车贷 4、不确定
     * @param $city
     * @return array
     */
    public static function getCar($car_info=''){
        switch ($car_info){
            case '000':
                $res=1;
                break;
            case '001':
                $res=2;
                break;
            case '002':
                $res=3;
                break;
            default:
                $res=4;
                break;
        }
        return $res;
    }

    /**
     * 获取职业
     * 1上班族 2公务员 3私企业主 4不确定
     * 001上班族, 002公务员, 003私营业主
     * @param string $occupation
     */
    public static function getOccupation($occupation = '')
    {
        switch ($occupation){
            case '001':
                $res=1;
                break;
            case '002':
                $res=2;
                break;
            case '003':
                $res=3;
                break;
            default:
                $res=4;
                break;
        }
        return $res;
    }

    /**
     * 1、银行卡发放 2、现金发放 3、不确定
     * 001银行转账, 002现金发放
     * @param string $salaryex
     * @return int
     */
    public static function getSalaryExtend($salaryex = '')
    {
        switch ($salaryex){
            case '001':
                $res=1;
                break;
            case '002':
                $res=2;
                break;
            default:
                $res=3;
                break;
        }
        return $res;
    }

    /**
     * 获取工资
     * @param string $salary
     * @return int
     */
    public static function getSalary($salary = '')
    {
        if ($salary == '101' || $salary=='102' || $salary=='103' || $salary=='104'){
            return 5000;
        }elseif ($salary == '105'){
            return 10000;
        }elseif ($salary == '106'){
            return 15000;
        }else{
            return 5000;
        }
    }

    /**
     * 社保公积金情况
     * @param string $socialSecurity
     * 1缴费未满一年 2缴费一年以上 3无社保 4不确定
     * @return int
     */
    public static function getSocSec($socsec=''){
        switch ($socsec){
            case '0':
                $res=3;
                break;
            case '1':
                $res=2;
                break;
            default:
                $res=4;
                break;
        }
        return $res;
    }

    /**
     * 微粒贷 1、 有 2、 无 3、 不确定
     * @param string $ismicro
     * @return int
     */

    public static  function  getIsmicro($ismicro=''){
        switch ($ismicro){
            case '0':
                $res=2;
                break;
            case '1':
                $res=1;
                break;
            default:
                $res=3;
                break;
        }
        return $res;
    }

    /**
     * 工作时间
     * 1现单位6个月以内 2现单位6-12个月  3现单位12-24个月 4现单位24个月以上 5不确定
     * 001 6个月内, 002 12个月内, 003 1年以上
     * @param string $ismicro
     * @return int
     */
    public static  function  getWorktime($worktime=''){
        switch ($worktime){
            case '001':
                $res=1;
                break;
            case '002':
                $res=2;
                break;
            case '003':
                $res=3;
                break;
            default:
                $res=5;
                break;
        }
        return $res;
    }

    /**
     * 保单
     *   1无 2年缴保费2400以下 3年缴保费2400以上 4、不确定
     *   0无, 1:2400以下，2:2400以上
     * @param string $ismicro
     * @return int
     */

    public static function getInsurance($insurance=''){
        switch ($insurance){
            case '0':
                $res=1;
                break;
            case '1':
                $res=2;
                break;
            case '2':
                $res=3;
                break;
            default:
                $res=4;
                break;
        }
        return $res;
    }


    /**
     * 贷款金额(万为单位)
     * @param string $ismicro
     * @return int
     */
    public static function getMoney($money=''){
      if($money>=10000 and $money<=20000){
          return 2;
      }
      if($money>=500000){
          return 50;
      }
      return $money/10000;
    }
    /**
     * 营业执照
     * 1注册一年以下 2注册一年以上 3不确定
     * 001 一年以内 002 一年以上
     * @param string $ismicro
     * @return int
     */
    public static function getZhizhao($business_licence=''){
        switch ($business_licence){
            case '001':
                $res=1;
                break;
            case '002':
                $res=2;
                break;
            default:
                $res=3;
                break;
        }
        return $res;
    }
}