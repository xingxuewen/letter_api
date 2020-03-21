<?php
namespace App\Services\Core\Oneloan\Cainiao\Config;

/**
 *  财鸟配置
 */
class CainiaoConfig
{

    //正式线渠道名
    const CHANNEL_NAME = '速贷之家API';
    //正式线渠道id
    const CHANNEL_ID = '182808';
    //测试线渠道名
    const TEST_CHANNEL_NAME = 'test1';
    //测试线渠道id
    const TEST_CHANNEL_ID = '560195';
    //正式环境
    const FORMAL_URL = 'https://api.cainiaodk.com/user/addOrder/' . self::CHANNEL_NAME . '/' . self::CHANNEL_ID;
    //测试环境
    const TEST_URL = 'https://test.cainiaodk.com/user/addOrder/' . self::TEST_CHANNEL_NAME . '/' . self::TEST_CHANNEL_ID;
    //对应真实环境
    const REAL_URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;

    /**
     * @param string $city
     * @return string $city
     */
    static public function getCity($city = ''){
        if (strstr($city, '市')) {
            $city = strstr($city, '市', true);
        }
        return $city;
    }

    /**
     * 获取毫秒时间戳
     *
     * @return string
     */
    public static function getMillionTime()
    {
        //获取毫秒时间
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec * 1000);
        $millisecond = str_pad($msec, 3, '0', STR_PAD_RIGHT);
        $milliTime = date("YmdHis") . $millisecond;

        return $milliTime;
    }

    /**
     * 获取职业
     * 1:上班族 2:公务员/事业编制 3:自由职业 4: 个体户 5:企业主
     * 备注: 上班族,公务员必须填写(社保,公积金,公司,工作年限,收入情况,收入形式)
     *      自由职业必填(收入情况,收入形式);个体户,企业主必填(营业执照,经营年限,年流水)
     * @param string $occupation
     */
    public static function getOccupation($occupation = '')
    {
        if ($occupation == '001'){
            return 1;
        }elseif ($occupation == '002'){
            return 2;
        }elseif ($occupation == '003'){
            return 4;
        }
    }

    /**
     * 工作年限：(0：0-3个月以下  1：3-6个月  2：6个月以上
     * @param string $worktime
     * @return int
     */
    public static function getWorkTime($worktime = '')
    {
        if ($worktime == '001'){
            return 1;
        }elseif ($worktime == '002'){
            return 2;
        }elseif ($worktime == '003'){
            return 2;
        }
    }

    /**
     * 获取工资
     * @param string $salary
     * @return int
     */
    public static function getSalary($salary = '')
    {
        if ($salary == '101'){
            return 2000;
        }elseif ($salary == '102'){
            return 3000;
        }elseif ($salary == '103'){
            return 4000;
        }elseif ($salary == '104'){
            return 5000;
        }elseif ($salary == '105'){
            return 10000;
        }elseif ($salary == '106'){
            return 15000;
        }
    }

    /**
     * 对返回结果进行处理
     *
     * @param string $code
     * @return mixed|string
     */
    public static function getMessage($code = '')
    {
        $msgArr = [
            '000' => '接收成功',
            '001' => '缺少必要参数',
            '002' => '签名有误',
            '003' => '申请重复',
            '004' => '接收异常',
            '008' => '未找到对应的城市',
        ];

        return isset($msgArr[$code]) ? $msgArr[$code] : '无错误信息';
    }

}
