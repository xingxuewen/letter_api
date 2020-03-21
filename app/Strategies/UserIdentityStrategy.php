<?php

namespace App\Strategies;

use App\Constants\UserIdentityConstant;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Services\Core\Store\Qiniu\QiniuService;


/**
 * Class UserIdentityStrategy
 * @package App\Strategies
 * 用户身份信息
 */
class UserIdentityStrategy extends AppStrategy
{
    /**
     * @param null $data
     * @return string
     * @desc    学历
     */
    public static function cerintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '中专/高中以下';
        elseif ($i == 2) return '统招大专';
        elseif ($i == 3) return '统考本科/自考本科';
        elseif ($i == 4) return '统招硕士及以上';
        else return '';
    }

    public static function cerstrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '中专/高中以下') return 1;
        elseif ($str == '统招大专') return 2;
        elseif ($str == '统考本科/自考本科') return 3;
        elseif ($str == '统招硕士及以上') return 4;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    单位性质
     */
    public static function comintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '国有企业/事业单位/政府机关';
        elseif ($i == 2) return '合资企业';
        elseif ($i == 3) return '外资企业';
        elseif ($i == 4) return '民营企业/个体工商户';
        else return '';
    }

    public static function comstrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '国有企业/事业单位/政府机关') return 1;
        elseif ($str == '合资企业') return 2;
        elseif ($str == '外资企业') return 3;
        elseif ($str == '民营企业/个体工商户') return 4;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    工作年限
     */
    public static function workintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '少于6个月';
        elseif ($i == 2) return '6~12个月';
        elseif ($i == 3) return '12~24个月';
        elseif ($i == 4) return '24个月以上';
        else return '';
    }

    public static function workstrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '少于6个月') return 1;
        elseif ($str == '6~12个月') return 2;
        elseif ($str == '12~24个月') return 3;
        elseif ($str == '24个月以上') return 4;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    企业邮箱
     */
    public static function emailintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '无';
        elseif ($i == 2) return '有';
        else return '';
    }

    public static function emailstrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '无') return 1;
        elseif ($str == '有') return 2;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    月收入
     */
    public static function monintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '5000以下';
        elseif ($i == 2) return '5000~8000';
        elseif ($i == 3) return '8001~10000';
        elseif ($i == 4) return '10000以上';
        else return '';
    }

    public static function monstrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '5000以下') return 1;
        elseif ($str == '5000~8000') return 2;
        elseif ($str == '8001~10000') return 3;
        elseif ($str == '10000以上') return 4;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    工资流水
     */
    public static function wageintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '无';
        elseif ($i == 2) return '有';
        else return '';
    }

    public static function wagestrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '无') return 1;
        elseif ($str == '有') return 2;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    学业信息
     */
    public static function stuintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '中专/高中及以下在读';
        elseif ($i == 2) return '大专在读';
        elseif ($i == 3) return '本科在读';
        elseif ($i == 4) return '硕士及以上在读';
        else return '';
    }

    public static function stustrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '中专/高中及以下在读') return 1;
        elseif ($str == '大专在读') return 2;
        elseif ($str == '本科在读') return 3;
        elseif ($str == '硕士及以上在读') return 4;
        else return 0;
    }


    /**
     * @param null $data
     * @return string
     * @desc    毕业年份
     */
    public static function graintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '余1年';
        elseif ($i == 2) return '余2年';
        elseif ($i == 3) return '余3年';
        elseif ($i == 4) return '余4年';
        else return '';
    }

    public static function grastrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '余1年') return 1;
        elseif ($str == '余2年') return 2;
        elseif ($str == '余3年') return 3;
        elseif ($str == '余4年') return 4;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    公司经营年限
     */
    public static function manintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '少于6个月';
        elseif ($i == 2) return '6~12个月';
        elseif ($i == 3) return '12~24个月';
        elseif ($i == 4) return '24个月以上';
        else return '';
    }

    public static function manstrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '少于6个月') return 1;
        elseif ($str == '6~12个月') return 2;
        elseif ($str == '12~24个月') return 3;
        elseif ($str == '24个月以上') return 4;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    营业执照
     */
    public static function busiintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '无';
        elseif ($i == 2) return '有';
        else return '';
    }

    public static function busistrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '无') return 1;
        elseif ($str == '有') return 2;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    收入来源
     */
    public static function incomeintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '无';
        elseif ($i == 2) return '有';
        else return '';
    }

    public static function incomestrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '无') return 1;
        elseif ($str == '有') return 2;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    近6个月对公账户流水
     */
    public static function billintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '无';
        elseif ($i == 2) return '有';
        else return '';
    }

    public static function billstrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '无') return 1;
        elseif ($str == '有') return 2;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    稳定的收入来源
     */
    public static function sourceintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '无';
        elseif ($i == 2) return '有';
        else return '';
    }

    public static function sourcestrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '无') return 1;
        elseif ($str == '有') return 2;
        else return 0;
    }

    /**证件号转化为汉字
     * @param $param
     * @return string
     */
    public static function certificateTypeIntToStr($param)
    {
        $i = DateUtils::toInt($param);
        if ($i == 0) return '身份证';
        else return '';
    }

    /**
     * @param string $param
     * @return string
     * 身份证号加密
     * 1****************3 共18位
     */
    public static function encryCertificateNo($param = '')
    {
        $param = mb_substr($param, 0, 1) . '****************' . mb_substr($param, -1);
        return $param ? $param : '';
    }

    /**
     * 加密处理身份证号码
     * 201932********2611
     * @param string $param
     * @return string
     */
    public static function formatCertificateNo($param = '')
    {
        $param = mb_substr($param, 0, 6) . '********' . mb_substr($param, -4);
        return $param ? $param : '';
    }

    /**
     * 加密处理身份证号码
     * 2019**********2611
     * @param string $param
     * @return string
     */
    public static function formatCertificateNoFour($param = '')
    {
        $param = mb_substr($param, 0, 4) . '****************' . mb_substr($param, -4);
        return $param ? $param : '';
    }

    /**
     * 生日
     * @param $param
     * @return string
     */
    public static function fetchBirthday($param)
    {
        return isset($param) ? $param['year'] . '-' . $param['month'] . '-' . $param['day'] : '';
    }

    /**
     * @param array $params
     * @return array
     * face++认证之后返回身份证正面信息
     */
    public static function getFaceToIdcardInfo($params = [])
    {
        $faceinfo = $params['faceinfo'];
        $data = [];
        $data['realname'] = isset($faceinfo['name']) ? $faceinfo['name'] : '';
        $data['sex'] = $faceinfo['gender'];
        $data['certificate_type'] = UserIdentityStrategy::certificateTypeIntToStr($params['certificate_type']);
        $data['certificate_no'] = $faceinfo['id_card_number'];

        return $data ? $data : [];
    }

    /**
     * @param string $string
     * @return mixed|string
     * 将1997.01.01转化成1997-01-01
     */
    public static function formatTimeToYmd($string = '')
    {
        if (empty($string)) {
            return '';
        }
        return str_replace('.', '-', $string);

    }

    /**
     * @param array $params
     * @return array
     * 活体认证成功返回用户信息
     */
    public static function getFaceAliveToIdcardInfo($params = [])
    {
        $data = [];
        $now = time();
        $date = floor((strtotime($params['card_endtime']) - $now) / 86400);
        $data['idcard_sign'] = 0;
        if ($date <= 30 && $date > 0) {
            //30天之内 即将过期
            $data['idcard_sign'] = 2;
        } elseif ($date <= 0) {
            //已过期
            $data['idcard_sign'] = 3;
        } else {
            $data['idcard_sign'] = 1;
        }
        $data['realname'] = isset($params['idcard_name']) ? $params['idcard_name'] : '';
        $data['sex'] = SexStrategy::intToStr($params['sex']);
        $data['certificate_type'] = UserIdentityStrategy::certificateTypeIntToStr($params['certificate_type']);
        $data['certificate_no'] = UserIdentityStrategy::encryCertificateNo($params['idcard_number']);
        $data['idcard_time'] = DateUtils::formatTimeToYmd($params['card_starttime']) . '-' . DateUtils::formatTimeToYmd($params['card_endtime']);

        return $data ? $data : [];
    }

    /**
     * 活体认证需要数据
     * @param $data
     * @param $realname
     * @return mixed
     *
     */
    public static function getAliveNeedDatas($data, $realname)
    {
        $data['idcard_number'] = $realname['certificate_no'];
        $data['idcard_name'] = $realname['realname'];
        $data['sex'] = $realname['sex'];
        $data['card_photo'] = QiniuService::getImgToFace($realname['card_photo']);
        $data['card_front'] = QiniuService::getImgToFace($realname['card_front']);
        $data['card_starttime'] = $realname['card_starttime'];
        $data['card_endtime'] = $realname['card_endtime'];
        $data['profile_id'] = $realname['profile_id'];

        return $data;
    }

    /**
     * face验证返回关于活体认证结果的错误信息
     * @param array $params
     * @return array|bool
     */
    public static function getFaceidErrorMeg($params = [])
    {
        //判断活体认证是否为本人
        $result_ref1 = isset($params['result_ref1']) ? $params['result_ref1'] : [];
        $result_ref2 = isset($params['result_ref2']) ? $params['result_ref2'] : [];
        $genuineness = isset($params['face_genuineness']) ? $params['face_genuineness'] : [];

        //比对结果的置信度，condifence>阈值，数字越大表示两张照片越可能是同一个人
        if ($result_ref1 && $result_ref1['confidence'] < $result_ref1['thresholds']['1e-4']) {
            return $data = ['error' => RestUtils::getErrorMessage(12000), 'code' => 12000];
        } elseif ($result_ref2 && $result_ref2['confidence'] < $result_ref2['thresholds']['1e-4']) {
            return $data = ['error' => RestUtils::getErrorMessage(12000), 'code' => 12000];
        } elseif ($genuineness['synthetic_face_confidence'] >= $genuineness['synthetic_face_threshold']) {
            //synthetic_face_confidence < synthetic_face_threshold 可以认为人脸不是软件合成脸
            return $data = ['error' => RestUtils::getErrorMessage(12001), 'code' => 12001];
        } elseif ($genuineness['screen_replay_confidence'] >= $genuineness['screen_replay_threshold']) {
            //如果screen_replay_confidence < screen_replay_threshold，可以认为人脸不是屏幕翻拍
            return $data = ['error' => RestUtils::getErrorMessage(12002), 'code' => 12002];
        } elseif ($genuineness['mask_confidence'] >= $genuineness['mask_threshold']) {
            //如果mask_confidence < mask_threshold，可以认为人脸不是面具
            return $data = ['error' => RestUtils::getErrorMessage(12003), 'code' => 12003];
        }
        return true;
    }

    /**
     * face++返回正面数据验证
     * @param array $params
     * @return array|bool
     */
    public static function getIdcardFrontErrorMeg($params = [])
    {
        //身份证号码验证
        $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
        if (!preg_match($regx, $params['id_card_number'])) {
            return $data = ['error' => RestUtils::getErrorMessage(12004), 'code' => 12004];
        }
        //性别验证
        $sexint = substr($params['id_card_number'], -2, 1);
        $sex = intval($sexint) % 2 === 0 ? '女' : '男';
        if ($sex != $params['gender']) {
            return $data = ['error' => RestUtils::getErrorMessage(12005), 'code' => 12005];
        }

        return true;

    }

    /**
     * face++返回反面数据验证
     * @param array $params
     * @return array|bool
     */
    public static function getIdcardBackErrorMeg($params = [])
    {
        $regx = "/^(\.|-|\d)*$/";
        if (!preg_match($regx, $params['valid_date'])) {
            return $data = ['error' => RestUtils::getErrorMessage(12006), 'code' => 12006];
        } elseif (strlen($params['valid_date']) != 21) {
            return $data = ['error' => RestUtils::getErrorMessage(12006), 'code' => 12006];
        }

        return true;
    }

    /**
     * 验证日期格式
     * 19970101-19700202
     *
     * @param string $param
     * @return array|bool
     */
    public static function getIdcardBackErrorMegUp($param = '')
    {
        $regx = "/^(\.|-|\d)*$/";
        if (!preg_match($regx, $param)) {
            return $data = ['error' => RestUtils::getErrorMessage(12006), 'code' => 12006];
        } elseif (strlen($param) != 17) {
            return $data = ['error' => RestUtils::getErrorMessage(12006), 'code' => 12006];
        }

        return true;
    }

    /**
     * 天创验证错身份证信息误提示
     * @param array $params
     * @return array|bool
     */
    public static function getTianErrorMeg($params = [])
    {
        if ($params['status'] != 0) {
            return array('error' => isset($params['data']['resultMsg']) ? $params['data']['resultMsg'] : '出错了', 'code' => 10005);
        } elseif ($params['status'] == 0 && $params['data']['result'] != 1) {
            //result Int 认证结果 1 认证成功 2 认证失败 3 未认证 4 已注销
            return array('error' => isset($params['data']['resultMsg']) ? $params['data']['resultMsg'] : '出错了', 'code' => 10005);
        }

        return true;
    }

    /**
     * 天创验证流水数据处理
     * @param array $data
     * @return array
     */
    public static function getUserRealnameLogData($data = [])
    {
        $params['faceinfo'] = $data['tianCheck'];
        $params['faceinfo']['id_card_number'] = $data['realname']['certificate_no'];
        $params['faceinfo']['name'] = $data['realname']['realname'];
        $params['status'] = UserIdentityConstant::AUTHENTICATION_STATUS_TIAN;
        $params['userId'] = $data['userId'];
        $params['type'] = UserIdentityConstant::AUTHENTICATION_TYPE_TIAN;
        $params['certificate_type'] = UserIdentityConstant::CERTIFICATE_TYPE_IDCARD;

        return $params ? $params : [];
    }

    /**
     * 掩码真实姓名
     * 赵丽影 => *丽影
     *
     * @param string $realname
     * @return mixed
     */
    public static function formatRealname($realname = '')
    {
        if (empty($realname)) return '';
        else return '*' . mb_substr($realname, 1);
    }

    /**
     * 虚假用户信息数据处理
     *
     * @param array $params
     * @return array
     */
    public static function fetchFakeRealname($params = [])
    {
        //根据身份证号获取性别、生日
        $sexs = Utils::getAgeAndBirthDayByCard($params['idcard']);
        $params['birthday'] = date('Y-n-j', strtotime($sexs['birthday'] . '00:00:00'));
        $params['sex'] = $sexs['sex'] == 1 ? 0 : 1;
        $params['certificate_type'] = 0;
        return $params ? $params : [];
    }

    /**
     * 用户数据处理
     *
     * @param array $params
     * @return array
     */
    public static function getSpreadUserInfo($params = [], $data = [])
    {
        //用户信息
        $userInfo = isset($params['user_info']) ? $params['user_info'] : [];
        //实名信息
        $realnameInfo = isset($params['realname_info']) ? $params['realname_info'] : [];
        //虚假实名信息
        $fakeRealInfo = isset($params['fake_info']) ? $params['fake_info'] : [];
        //定位信息
        $location = isset($params['location']) ? $params['location'] : [];

        //真实信息
        $datas['real_name'] = isset($realnameInfo['name']) ? $realnameInfo['name'] : '';
        $datas['real_certificate_no'] = isset($realnameInfo['certificate_no']) ? $realnameInfo['certificate_no'] : '';
        $datas['real_sex'] = isset($realnameInfo['sex']) ? $realnameInfo['sex'] : 0;
        $datas['real_birthday'] = isset($realnameInfo['birthday']) ? $realnameInfo['birthday'] : '';
        //虚假信息
        $datas['fake_name'] = isset($fakeRealInfo['name']) ? $fakeRealInfo['name'] : '';
        $datas['fake_certificate_no'] = isset($fakeRealInfo['certificate_no']) ? $fakeRealInfo['certificate_no'] : '';
        $datas['fake_sex'] = isset($fakeRealInfo['sex']) ? $fakeRealInfo['sex'] : 0;
        $datas['fake_birthday'] = isset($fakeRealInfo['birthday']) ? $fakeRealInfo['birthday'] : '';
        //定位信息
        if (isset($location['user_city'])) $city = str_replace('市', '', $location['user_city']);
        $datas['city'] = isset($city) ? $city : '';

        $datas['mobile'] = isset($userInfo['mobile']) ? $userInfo['mobile'] : '';
        $datas['sd_user_id'] = isset($data['userId']) ? $data['userId'] : '';
        $datas['userId'] = isset($data['userId']) ? $data['userId'] : '';
        $datas['username'] = isset($userInfo['username']) ? $userInfo['username'] : '';

        return $datas ? $datas : [];
    }

    /**
     * 根据类型获取实名认证完整步骤状态值
     * 【face：face；天创：tianchuang；活体：end】
     * 【9通过,1face通过,2天创通过,3活体通过,4公安部通过】
     *
     * @param array $data
     * @return int
     */
    public static function getRealnameStep($data = [])
    {
        $type = isset($data['realnameType']) ? $data['realnameType'] : '';
        //根据步骤判断状态值
        switch ($type) {
            //完整状态
            case 'end':
                $step = UserIdentityConstant::AUTHENTICATION_STATUS_FINAL;
                break;
            //天创验证
            case 'tianchuang':
                $step = UserIdentityConstant::AUTHENTICATION_STATUS_TIAN;
                break;
            //face扫描
            case 'face':
                $step = UserIdentityConstant::AUTHENTICATION_STATUS_FACE;
                break;
            default:
                $step = UserIdentityConstant::AUTHENTICATION_STATUS_FINAL;

        }

        return $step;
    }

    /**
     * Megvii 扫描身份证 正反面信息数据处理
     *
     * @param array $datas
     * @return mixed
     */
    public static function getMegviiCardInfo($datas = [])
    {
        //正面信息
        $info['request_id'] = isset($datas['request_id']) ? $datas['request_id'] : '';
        $info['id_card_number'] = isset($datas['idcard_number']['result']) ? $datas['idcard_number']['result'] : '';
        $info['name'] = isset($datas['name']['result']) ? $datas['name']['result'] : '';
        $info['gender'] = isset($datas['gender']['result']) ? $datas['gender']['result'] : '';
        $info['address'] = isset($datas['address']['result']) ? $datas['address']['result'] : '';
        $info['birthday']['year'] = isset($datas['birth_year']['result']) ? $datas['birth_year']['result'] : '';
        $info['birthday']['month'] = isset($datas['birth_month']['result']) ? $datas['birth_month']['result'] : '';
        $info['birthday']['day'] = isset($datas['birth_day']['result']) ? $datas['birth_day']['result'] : '';
        $info['race'] = isset($datas['nationality']['result']) ? $datas['nationality']['result'] : '';
        //反面信息
        $info['card_starttime'] = isset($datas['valid_date_start']['result']) ? DateUtils::formatBirthdayToYmd($datas['valid_date_start']['result']) : '';
        $info['card_endtime'] = isset($datas['valid_date_end']['result']) ? DateUtils::formatBirthdayToYmd($datas['valid_date_end']['result']) : '';
        $info['valid_date'] = $datas['valid_date_start']['result'] . '-' . $datas['valid_date_end']['result'];
        $info['issued_by'] = isset($datas['issued_by']['result']) ? $datas['issued_by']['result'] : '';
        $info['legality'] = isset($datas['legality']) ? $datas['legality'] : '';

        return $info;
    }

    /**
     * 活体验证是否为本人判断依据
     *
     * @param array $params
     * @return array|bool
     */
    public static function getMegviiAliveErrorMeg($params = [])
    {
        //判断活体认证是否为本人
        $verification = isset($params['verification']) ? $params['verification'] : [];

        //比对结果的置信度，condifence>阈值，数字越大表示两张照片越可能是同一个人
        if ($verification && $verification['confidence'] < $verification['thresholds']['1e-4']) {
            return $data = ['error' => RestUtils::getErrorMessage(12000), 'code' => 12000];
        }


        return true;

    }

}