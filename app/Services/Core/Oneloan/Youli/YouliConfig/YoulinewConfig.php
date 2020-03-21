<?php


namespace App\Services\Core\Oneloan\Youli\YouliConfig;

use App\Models\Orm\UserAgent;
use App\Helpers\UserAgent AS UserAgentUtil;
use App\Helpers\Utils;
class YoulinewConfig
{
    //前置机地址
    const URL = PRODUCTION_ENV ? 'http://www.shenchao218.com/v2/' : 'http://test.shenchao218.com/v2/';

    //渠道号
    const AID ='zhijie';

    /**
     * 获取参数
     *
     * @param $params
     * @return array
     */
    public static function getParams($params)
    {
        $income = self::getSalary($params);
        $ishouse = 2;
        $iscar = 2;
        $ins=2;
        $hf=2;
        $si=2;
        if(isset($params['house_info']) && $params['house_info'] != '000')
        {
            $ishouse = 1;
        }

        if(isset($params['car_info']) && $params['car_info'] != '000')
        {
            $iscar = 1;
        }
        if(!empty($params['has_insurance'])){
            $ins=1;
        }
        if(isset($params['accumulation_fund']) && ($params['accumulation_fund']=='001' ||  $params['accumulation_fund']=='002')){
            $hf=1;
        }
        if(isset($params['social_security']) and $params['social_security']==1){
            $si=1;
        }
        $user_agent = UserAgent::Select(['user_agent'])->where('user_id', '=', $params['user_id'])->orderBy('create_at','Desc')->first();

        return [
            'aid'=>self::AID,
            'name' => isset($params['name']) ? $params['name'] : '',
            'phone' => isset($params['mobile']) ? $params['mobile'] : '',
            'idCard' => isset($params['certificate_no']) ? $params['certificate_no'] : '',
            'gender'=>isset($params['sex'])?self::getSex($params):1,
            'birth'=>date('Y-m-d',strtotime($params['birthday'])),
            'province'=>$params['pro_code'],
            'city'=>$params['city_code'],
            'loan' => isset($params['money']) ? $params['money'] : '',
            'income' => $income,
            'ishouse' => $ishouse,
            'iscar' => $iscar,
            'ins'=>$ins,
            'hf'=>$hf,
            'si'=>$si,
            'ip'=> isset($params['created_ip']) ?  explode(',',$params['created_ip'])[0] : explode(',',Utils::ipAddress())[0],
            'ua'=> isset($user_agent['user_agent']) ? $user_agent['user_agent'] : UserAgentUtil::i()->getUserAgent(),
            'cartype'=>''
        ];
    }




    /**
     * 性别
     *
     * @param $params
     * @return string
     */
    public static function getSex($params){
        switch ($params['sex']){
                case 0:
                    $gender=2;
                    break;
                case 1:
                    $gender=1;
                    break;
                default:
                   $gender=1;
        }
        return $gender;
    }


    /**
     * 收入
     *
     * @param $params
     * @return string
     */
    public static function getSalary($params)
    {
        switch ($params['salary'])
        {
            case '001':
                $income = '2000';
                break;
            case '002':
                $income = '3500';
                break;
            case '003':
                $income = '7500';
                break;
            case '004':
                $income = '10000';
                break;
            default:
                $income = '2000';
        }

        return $income;
    }


}