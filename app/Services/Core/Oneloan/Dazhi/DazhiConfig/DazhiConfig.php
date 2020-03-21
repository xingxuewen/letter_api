<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-11-8
 * Time: 下午
 */
namespace App\Services\Core\Oneloan\Dazhi\DazhiConfig;
use App\Helpers\Logger\SLogger;

class DazhiConfig{

    const TEST_QUDAO_ID=2824;
    const TEST_SOURCE_ID=10394;

    const FORMAL_QUDAO_ID=2824;
    const FORMAL_SOURCE_ID=10393;

    const KEY='dzjf2kals4qpc7p9Loan';

    const SOURCE_ID = PRODUCTION_ENV ? self::FORMAL_SOURCE_ID : self::TEST_SOURCE_ID;
    const QUDAO_ID = PRODUCTION_ENV ? self::FORMAL_QUDAO_ID : self::TEST_QUDAO_ID;


    //测试环境
    const TEST_URL='https://test.dazhii.com/basic/loan/api/reg?c='.self::TEST_QUDAO_ID.'&t=loan&b=mobile';
    //正式环境
    const FORMAL_URL='https://40486693.dazhii.com/basic/loan/api/reg?c='.self::FORMAL_QUDAO_ID.'&t=loan&b=mobile';
    const USE_URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;


    public static function getV($birth,$mobile)
    {
//        logError($birth.$mobile.self::QUDAO_ID.self::KEY);
        $v=strtoupper(md5($birth.$mobile.self::QUDAO_ID.self::KEY));
        return $v;
    }
    /**
     * 获取性别[我方1男 0女;对方：1男2女]
     * @param string $sex
     * @return string $sex
     */
    static public function getSex($sex = 0){
        if($sex==0){
            return 2;
        }else{
            return 1;
        }
    }

    /**
     * 1有房贷，2 红本在手， 3 无房
     * 000无房, 001有房贷, 002无房贷
     * @param $city
     * @return array
     */
    public static function getHouse($house_info){
        switch ($house_info){
            case '000':
                $h=3;
                break;
            case '001':
                $h=1;
                break;
            case '002':
                $h=2;
                break;
            default:
                $h=3;
                break;
        }
        return $h;
    }
    /**
     * 1有车贷，2 有车无贷， 3 无车
     * 000无车, 001有车贷, 002无车贷
     * @param $city
     * @return array
     */
    public static function getCar($car_info=''){
        switch ($car_info){
            case '000':
                $h=3;
                break;
            case '001':
                $h=1;
                break;
            case '002':
                $h=2;
                break;
            default:
                $h=3;
                break;
        }
        return $h;
    }
    /**
     * 信用卡1有，2无(我方：0无, 1有)
     *
     * @param $city
     * @return array
     */
    public static function getCredit($has_creditcard=''){
        if($has_creditcard==1){
            return 1;
        }else{
            return 2;
        }
    }

    /**
     * 获取工资发放形式(我方001银行转账, 002现金发放)
     * @param string $salaryex 1：银行转账，2：现金发放
     * @return int
     */
    public static function getSalaryExtend($salaryex = '')
    {
        if($salaryex=='001'){
            return 1;
        }elseif($salaryex=='002'){
            return 2;
        }else{
            return 1;
        }
    }

    /**
     * 获取职业(001上班族, 002公务员, 003私营业主)
     * 1：工薪族 2：公务员，3：自雇，4：其它，5:个体户，6：企业主
     * @param string $occupation
     */
    public static function getOccupation($occupation = '')
    {
        if ($occupation == '001'){
            return 1;
        }elseif ($occupation == '002'){
            return 2;
        }elseif ($occupation == '003'){
            return 5;
        }else{
            return 4;
        }
    }


    /**
     * 获取工资(101:2千以下，102:2千-3千，103:3千-4千，104:4千-5千，105:5千-1万，106:1万以上)
     * @param string $salary 1:5000以下，2:5000-10000,3:10000-20000,4:20000以上
     * @return int
     */
    public static function getSalary($salary = '')
    {
        if ($salary == '101' || $salary=='102' || $salary=='103' || $salary=='104'){
            return 1;
        }elseif ($salary == '105'){
            return 2;
        }elseif ($salary == '106'){
            return 3;
        }
    }

    /**
     * 社保情况
     * @param string 0 无, 1 有
     * @return int 1:无社保，2：半年内，3：超半年
     */
    public  static function getSocial($social=''){
        if($social==0){
            return 1;
        } elseif($social==1){
            return 3;
        }else{
            return 1;
        }
    }

    /**
     * 公基金情况
     * @param string 000 无公积金, 001 1年以内, 002 1年以上
     * @return int 1:无公积金，2：半年内，3：超半年
     */
    public  static function getFound($social=''){
        if($social=='000'){
            return 1;
        } elseif($social=='001' || $social=='002'){
            return 3;
        }else{
            return 1;
        }
    }
    /**
     *
     * @param string 有无保单, 0无, 1:2400以下，2:2400以上
     * @return int 是否有寿险保单：1:无; 2：年缴2400内; 3：年缴超2400
     */
    public static function getInsurance($insurance){
        switch ($insurance){
            case 0:
                $res=1;
                break;
            case 1:
                $res=2;
                break;
            case 2:
                $res=3;
                break;
            default:
                $res=1;
                break;
        }
        return $res;
    }
}