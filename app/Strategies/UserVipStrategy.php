<?php

namespace App\Strategies;

use App\Constants\PaymentConstant;
use App\Constants\UserVipConstant;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\Factory\PaymentFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserOrderFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Factory\UserVipFactory;
use App\Models\Orm\UserVip;
use App\Services\AppService;
use App\Services\Core\Payment\PaymentService;
use App\Services\Core\Store\Qiniu\QiniuService;

/**
 * Class UserVipStrategy
 * @package App\Strategies
 * 会员策略层
 */
class UserVipStrategy extends AppStrategy
{
    /**
     * 会员判断
     *
     * @param $userId
     * @param $terminalType
     * @return array
     */
    public static function isUserVipAgain($userId, $terminalType)
    {
        $params = [];
        if (!empty($userId)) {
            $data = UserVipFactory::getUserVip($userId);
            if (!empty($data)) {
                $loanVipArr = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
                $data['productIds'] = $loanVipArr;
                $data['terminalType'] = $terminalType;
                $params['totalPriceTime'] = date('Y-m-d', strtotime($data['end_time']));
                //date('Y', strtotime($data['end_time'])).'年'.date('m', strtotime($data['end_time'])).'月'.date('d', strtotime($data['end_time'])).'日到期';
                $params['loanVipCount'] = ProductFactory::fetchProductCounts($data);
                $params['creditCount'] = UserReportFactory::getUserReportCount($userId);
                $params['isVipUser'] = 1;
            }
        }

        if (empty($params)) {
            $arr['isVipUser'] = 0;
            //vip产品数
            $arr['loanVipCount'] = '';
            //闪信免费查个数
            $arr['creditCount'] = '';
            //会员到期时间
            $arr['totalPriceTime'] = '';
            //会员动态
            $arr['memberActivity'] = UserVipStrategy::getMemberActivityInfo();
        } else {
            $arr['isVipUser'] = $params['isVipUser'];
            $arr['loanVipCount'] = $params['loanVipCount'];
            $arr['creditCount'] = $params['creditCount'];
            $arr['totalPriceTime'] = DateUtils::formatDateToLeftdata($params['totalPriceTime']);
            $arr['memberActivity'] = [];
        }

        //价格
        $arr['totalPrice'] = UserVipFactory::getVipAmount() . '/年';
        $arr['totalNoPrice'] = UserVipConstant::MEMBER_PRICE . '/年';
        //单纯显示价格
        $arr['totalPriceNum'] = UserVipFactory::getVipAmount() . '';
        $arr['totalNoPriceNum'] = UserVipConstant::MEMBER_PRICE . '';

        return $arr;
    }

    /**
     * 获取会员特权
     *
     * @return array
     */
    public static function getVipPrivilege()
    {
        $vipId = UserVipFactory::getVipTypeId();
        $pids = UserVipFactory::getVipPrivilegeIds($vipId);
        $res = [];
        //特权类型主id
        $priTypeId = UserVipFactory::fetchVipPrivilegeIdByNid(UserVipConstant::VIP_PRIVILEGE_DEFAULT);
        foreach ($pids as $pid) {
            $arr = UserVipFactory::getVipPrivilegeInfo($pid, $priTypeId);
            if ($arr) {
                $arr['img_link'] = QiniuService::getImgs($arr['img_link']);
            }
            unset($arr['created_at']);
            unset($arr['created_id']);
            unset($arr['updated_at']);
            unset($arr['updated_id']);
            unset($arr['status']);
            unset($arr['is_desc']);
            unset($arr['is_description']);
            $res[] = $arr;
        }
        //删除空数组
        $res = array_filter($res);
        $res = array_values($res);

        return $res;
    }

    /**
     * 根据vip类型的不同处理数据
     *
     * @param $message
     * @param $vipType
     * @return array
     */
    public static function getDiffVipTypeDeal($message, $vipType)
    {
        switch ($vipType) {
            case UserVipConstant::VIP_TYPE_NID:
                if (!empty($message) && $message['status'] == 1) {
                    $endTime = strtotime($message['end_time']);
                    if ($endTime > time()) {
                        $timeStamp = $endTime + (PaymentFactory::getVipTime() * 24 * 60 * 60);
                        $data['time'] = date('Y-m-d H:i:s', $timeStamp);
                    } else {
                        $data['time'] = date('Y-m-d H:i:s', UserVipStrategy::getVipExpired());
                    }
                } else {
                    $data['time'] = date('Y-m-d H:i:s', UserVipStrategy::getVipExpired());
                }
                break;
            //年度会员
            case UserVipConstant::VIP_ANNUAL_MEMBER:
                $nid = UserVipConstant::VIP_ANNUAL_MEMBER;
                if (!empty($message) && $message['status'] == 1) {
                    $endTime = strtotime($message['end_time']);
                    if ($endTime > time()) {
                        $timeStamp = $endTime + (PaymentFactory::getSubVipTimeByNid($nid) * 24 * 60 * 60);
                        $data['time'] = date('Y-m-d H:i:s', $timeStamp);
                    } else {
                        $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                    }
                } else {
                    $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                }
                break;
            //季度会员
            case UserVipConstant::VIP_QUARTERLY_MEMBER:
                $nid = UserVipConstant::VIP_QUARTERLY_MEMBER;
                if (!empty($message) && $message['status'] == 1) {
                    $endTime = strtotime($message['end_time']);
                    if ($endTime > time()) {
                        $timeStamp = $endTime + (PaymentFactory::getSubVipTimeByNid($nid) * 24 * 60 * 60);
                        $data['time'] = date('Y-m-d H:i:s', $timeStamp);
                    } else {
                        $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                    }
                } else {
                    $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                }
                break;
            //月度会员
            case UserVipConstant::VIP_MONTHLY_MEMBER:
                $nid = UserVipConstant::VIP_MONTHLY_MEMBER;
                if (!empty($message) && $message['status'] == 1) {
                    $endTime = strtotime($message['end_time']);
                    if ($endTime > time()) {
                        $timeStamp = $endTime + (PaymentFactory::getSubVipTimeByNid($nid) * 24 * 60 * 60);
                        $data['time'] = date('Y-m-d H:i:s', $timeStamp);
                    } else {
                        $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                    }
                } else {
                    $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                }
                break;
            default:
                $data = [];
        }

        return $data;
    }


    // by xuyj new v3.2.3
    public static function getDiffVipTypeDeal_new($message, $vipType)
    {
        switch (intval($vipType)) {
            case UserVipConstant::VIP_TYPE_NID:
                if (!empty($message) && $message['status'] == 1) {
                    $endTime = strtotime($message['end_time']);
                    if ($endTime > time()) {
                        $timeStamp = $endTime + (PaymentFactory::getVipTime() * 24 * 60 * 60);
                        $data['time'] = date('Y-m-d H:i:s', $timeStamp);
                    } else {
                        $data['time'] = date('Y-m-d H:i:s', UserVipStrategy::getVipExpired());
                    }
                } else {
                    $data['time'] = date('Y-m-d H:i:s', UserVipStrategy::getVipExpired());
                }
                break;
            //年度会员
            case 1:
                $nid = UserVipConstant::VIP_ANNUAL_MEMBER;
                if (!empty($message) && $message['status'] == 1) {
                    $endTime = strtotime($message['end_time']);
                    if ($endTime > time()) {
                        $timeStamp = $endTime + (PaymentFactory::getSubVipTimeByNid($nid) * 24 * 60 * 60);
                        $data['time'] = date('Y-m-d H:i:s', $timeStamp);
                    } else {
                        $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                    }
                } else {
                    $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                }
                break;
            //季度会员
            case 2 :
                $nid = UserVipConstant::VIP_QUARTERLY_MEMBER;
                if (!empty($message) && $message['status'] == 1) {
                    $endTime = strtotime($message['end_time']);
                    if ($endTime > time()) {
                        $timeStamp = $endTime + (PaymentFactory::getSubVipTimeByNid($nid) * 24 * 60 * 60);
                        $data['time'] = date('Y-m-d H:i:s', $timeStamp);
                    } else {
                        $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                    }
                } else {
                    $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                }
                break;
            //月度会员
            case 3:
                $nid = UserVipConstant::VIP_MONTHLY_MEMBER;
                if (!empty($message) && $message['status'] == 1) {
                    $endTime = strtotime($message['end_time']);
                    logInfo("iiiiiiiiiiiiiiiiiiiiiiiiiii", $endTime);

                    if ($endTime > time()) {
                        $timeStamp = $endTime + (PaymentFactory::getSubVipTimeByNid($nid) * 24 * 60 * 60);
                        $data['time'] = date('Y-m-d H:i:s', $timeStamp);
                    } else {
                        $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                    }
                } else {
                    $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
                }
                break;
            default:
                $data = [];
        }

        return $data;
    }

    /**
     * 统计数值
     *
     * @param $typeId
     * @return mixed
     */
    public static function getReStatistics($typeId)
    {
        $data = [];
        $ids = UserVipFactory::getPrivilegeId($typeId);
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $lege = UserVipFactory::getPrivilege($id);
                if (!empty($lege) || $lege['type_nid'] == UserVipConstant::MEMBER_COMMON_LOAN_PRODUCT_NID) {
                    $data['count'] = $lege['value'];
                } elseif (!empty($lege) || $lege['type_nid'] == UserVipConstant::MEMBER_VIP_LOAN_PRODUCT_NID) {
                    $data['count'] = $lege['value'];
                }
            }
        }

        return $data;
    }

    /**
     * 是会员返回到期时间
     *
     * @param $userId
     * @return false|string
     */
    public static function isUserVip($userId)
    {
        $time = "";
        if (!empty($userId)) {
            $data = UserVipFactory::getUserVip($userId);
            if (!empty($data)) {
                $time = date('Y', strtotime($data['end_time'])) . '年' . date('m', strtotime($data['end_time'])) . '月' . date('d', strtotime($data['end_time'])) . '日到期';
            }
        }

        return $time;
    }

    /**
     * 获取会员动态信息
     *
     * @return array
     */
    public static function getMemberActivityInfo()
    {
        $userids = UserVipStrategy::getRandUserId();
        $userData = [];
        foreach ($userids as $uid) {
            $users = UserVipFactory::getUser($uid['user_id']);
            if (!empty($users)) {
                $message = UserVipStrategy::getRandContent();
                $userData[] = UserVipStrategy::getMemberActivityData($uid['user_id'], $message, $users);;
            } else {
                $userData = [];
            }

        }

        $len = count($userData);
        if ($len < 10) {
            $limit = 10 - $len;
            $ids = UserVipFactory::getUserLimit($limit);
            foreach ($ids as $id) {
                $user = UserVipFactory::getUser($id['user_id']);
                $message = UserVipStrategy::getRandContent();
                $userData[] = UserVipStrategy::getMemberActivityData($id['user_id'], $message, $user);
            }
        }

        return $userData;
    }

    /**
     * 获取公共数据
     *
     * @param $userId
     * @param $message
     * @param $user
     * @return mixed
     */
    public static function getMemberActivityData($userId, $message, $user)
    {
        $data = UserStrategy::replaceUsernameSd($user);
        $data['photo'] = QiniuService::getImgToPhoto(UserVipFactory::getUserInfo($userId));
        $data['message'] = $message['content'];
        $data['money'] = $message['money'];
        $data['minute'] = UserVipStrategy::getRandMinute();
        $data['uid'] = $userId;

        return $data;
    }

    /**
     * 随机获取时间
     *
     * @return string
     */
    public static function getRandMinute()
    {
        return rand(1, 60) . '分钟前';
    }

    /**
     * 获取语句
     *
     * @return string
     */
    public static function getRandContent()
    {
        $reArr['money'] = "";
        $arr = [
            '通过会员服务疯狂下款',
            '已开通会员',
        ];

        $key = array_rand($arr, 1);

        if ($key == 0) {
            $reArr['money'] = rand(2, 11) . '000元';
        }

        $reArr['content'] = $arr[$key];

        return $reArr;
    }

    /**
     * 获取随机10个用户ID
     * @return mixed
     */
    public static function getRandUserId()
    {
        //获取user_vip总数
        $userVips = UserVipFactory::getUserVipCount();
        if ($userVips > 10) {
            $userIds = UserVipFactory::getUserVipLimit();
        } else {
            //从user_auth中随机获取十个
            $userIds = UserVipFactory::getUserLimit();
        }

        return $userIds;
    }

    /**
     * 获取用户订单一些参数
     *
     * @return array
     */
    public static function getUserOrderOtherParams()
    {
        return [
            'order_type' => UserOrderFactory::getOrderType(),  //订单类型
            'payment_type' => UserOrderFactory::getPaymentType(),  //支付类型
            'amount' => UserVipFactory::getReVipAmount(UserVipConstant::VIP_TYPE_NID), //金额
        ];
    }

    /**
     * 获取用户订单一些参数
     *
     * @param array $params
     * @return array
     */
    public static function getUserOrderOtherParamsByParam($params = [])
    {
        logInfo("$$$$$$$$ type,", $params);
        return [
            'order_type' => UserOrderFactory::getOrderType($params['type']),  //订单类型
            'payment_type' => UserOrderFactory::getPaymentType($params['payment_nid']),  //支付类型
            'amount' => $params['amount'], //金额
        ];
    }

    public static function getUserOrderOtherParamsByParam_new($params = [])
    {
        logInfo("$$$$$$$$ type = .", $params);
        return [
            'order_type' => UserOrderFactory::getOrderType($params['type']),  //订单类型
            'payment_type' =>UserOrderFactory::getPaymentType("HJZFNEW"),// UserOrderFactory::getPaymentType($params['payment_nid']),  //支付类型
            'amount' => $params['amount'],//UserVipFactory::getReVipAmountByNid($params['subtypeNid']), //金额
        ];
    }

    /**
     * 获取vip展示年
     *
     * @return string
     */
    public static function getVipYeer()
    {
        $day = PaymentFactory::getVipTime();
        return number_format(($day / 365), 1);
    }

    /**
     * 获取过期时间
     *
     * @return false|string
     */
    public static function getVipExpired()
    {
        $time = PaymentFactory::getVipTime();
        $lastDay = time() + ($time * 24 * 60 * 60);

        return $lastDay;
    }

    /**
     * 获取vip状态
     *
     * @param $orderStatus
     * @return int
     */
    public static function getVipStatus($orderStatus = 0)
    {
        switch ($orderStatus) {
            case 0:
                $vipStatus = 0;  //禁用
                break;
            case 1:
                $vipStatus = 1;  //使用
                break;
            case 5:
                $vipStatus = 4;  //处理中
                break;
            default:
                $vipStatus = 0;  //禁用
        }

        return $vipStatus;
    }

    /**
     * 汇聚支付状态转化为
     *
     * @param int $orderStatus
     * @return int
     */
    public static function formatHuijuReturnStatusToVipStatus($orderStatus = 0)
    {
        switch ($orderStatus) {
            case 0:
                $vipStatus = 0;  //禁用
                break;
            case 100:
                $vipStatus = 1;  //使用
                break;
            case 101:
                $vipStatus = 5;  //处理中
                break;
            case 102:
                $vipStatus = 1;  //使用
                break;
            default:
                $vipStatus = 0;  //禁用
        }


        return $vipStatus;
    }

    /**
     * 获取易宝订单一些参数值
     *
     * @param string $amount
     * @return array
     */
    public static function getYibaoOtherParams()
    {
        return [
            'amount' => UserVipFactory::getReVipAmount(UserVipConstant::VIP_TYPE_NID) * 100,
            'productname' => UserVipConstant::ORDER_DEALER_NAME . ' - ' . UserVipConstant::ORDER_PRODUCT_NAME,
            'productdesc' => UserVipConstant::ORDER_DESC,
            'url_params' => UserVipConstant::ORDER_TYPE,
        ];
    }

    /**
     * 获取易宝订单一些参数值
     *
     * @param array $params
     * @return array
     */
    public static function getYibaoOtherParamsByParam($params = [])
    {
        return [
            //支付金额
            'amount' => UserVipFactory::getReVipAmountByNid($params['subtypeNid']) * 100,
            'productname' => UserVipConstant::ORDER_DEALER_NAME . ' - ' . UserVipConstant::ORDER_PRODUCT_NAME,
            'productdesc' => UserVipConstant::ORDER_DESC,
            //url参数
            'url_params' => empty($params['type']) ? UserVipConstant::ORDER_TYPE : $params['type'],
        ];
    }


    /**
     * 获取汇聚订单一些参数值
     *
     * @param array $params
     * @return array
     * by xuyj v3.2.3
     */
    public static function getHuiJuOtherParamsByParam_new($params = [])
    {
        return [
            //支付金额
            'amount' => sprintf("%.2f", UserVipFactory::getReVipAmountByNid($params['subtypeNid'])),
            'productname' => UserVipConstant::ORDER_DEALER_NAME . ' - ' . UserVipConstant::ORDER_PRODUCT_NAME,
            'productdesc' => UserVipConstant::ORDER_DESC,
            'orderNo' => $params['order_id'],
            'url_params' => json_encode(['type' => $params['type'], 'vip_type' => $params['subtypeNid']]),
        ];
    }



    /**
     * 获取会员编号
     *
     * @param int $lastId 最后一个ID
     * @param string $prefix 前缀
     * @param string $name 名称
     * @param int $num 编号数字
     * @return string
     */
    public static function generateId($lastId, $name = 'VIP', $prefix = 'SD', $num = 8)
    {
        //获取毫秒时间
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec * 1000);
        $millisecond = str_pad($msec, 3, '0', STR_PAD_RIGHT);
        $timeLength = date("YmdHis") . $millisecond;

        $length = PaymentConstant::PAYMENT_PRODUCT_NUMBER_LENGTH - strlen(trim($prefix)) - strlen(trim($name)) - strlen(trim($timeLength)) - $num - 2;

        //如果还有多余的长度获取随机字符串
        $str = '';
        if ($length >= 0) {
            $str = PaymentService::i()->getRandString($length);
        } else {
            $name = substr($name, 0, $length);
        }

        //获取数字
        $strNum = sprintf("%0" . $num . "d", ($lastId + 1)); //UserVipFactory::getVipLastId()

        return $prefix . '-' . $name . '-' . $str . $timeLength . $strNum;
    }

    /**
     * 会员等级
     * @param string $vipNid
     * @return int
     */
    public static function getVipGrade($vipNid = '')
    {
        $vipNid = trim($vipNid);
        switch ($vipNid) {
            //普通会员
            case UserVipConstant::VIP_TYPE_NID:
                $vipGrade = 1;
                break;
            default :
                $vipGrade = 0;

        }
        return $vipGrade;

    }

    /**
     * 随机获取动态内容
     * 可以随时修改动态内容
     *
     * @param array $params
     * @return mixed
     */
    public static function getRandMessage($params = [])
    {
        $reArr['money'] = "";

        $key = array_rand($params, 1);
        if ($key == 0) {
            $reArr['money'] = rand(2, 11) . '000';
        }

        $reArr['content'] = $params[$key];

        return $reArr;
    }

    /**
     * 立即续费按钮展示情况
     * 在一个月内展示
     * 到期时间大于一个月不展示
     *
     * @param string $expire
     * @return int
     */
    public static function checkIsShowPriceTime($expire = '')
    {
        if (empty($expire)) //没有到期时间
        {
            $isShow = 0;
        }

        //前一个月时间
        $beforMonth = date('Y-m-d', strtotime("$expire -1 month"));
        //当前时间
        $nowDate = date('Y-m-d', time());

        if ($nowDate > $beforMonth && $nowDate < $expire) $isShow = 1;
        else $isShow = 0;

        return $isShow;
    }

    /**
     * 立即续费按钮展示情况
     * 在一个月内展示
     * 到期时间大于一个月不展示
     *
     * @param string $expire
     * @return int
     */
    public static function checkIsShowPriceTimeBySevenDays($expire = '')
    {
        if (empty($expire)) //没有到期时间
        {
            $isShow = 0;
        }

        //前一个星期时间
        $beforMonth = date('Y-m-d', strtotime("$expire -7 days"));
        //当前时间
        $nowDate = date('Y-m-d', time());

        if ($nowDate > $beforMonth && $nowDate < $expire) $isShow = 1;
        else $isShow = 0;

        return $isShow;
    }

    /**
     * 八大特权数据处理
     *
     * @param array $privileges
     * @param array $data
     * @return array
     */
    public static function getVipPrivileges($privileges = [], $data = [])
    {
        if (empty($privileges)) return [];
        foreach ($privileges as $key => $val) {
            //唯一标识为vip_product_diff_count，需要替换数据
            if ($val['type_nid'] == UserVipConstant::VIP_PRODUCT_DIFF_COUNT) {
                $privileges[$key]['subtitle'] = str_replace('40', $data['vip_diff_count'], $val['subtitle']);
            }
            //图片地址
            $privileges[$key]['img_link'] = QiniuService::getImgs($val['img_link']);
        }

        return $privileges ? $privileges : [];
    }

    /**
     * 充值列表数据处理
     *
     * @param array $recharges
     * @return array
     */
    public static function getRecharges($recharges = [])
    {
        foreach ($recharges as $key => $val) {
            $recharges[$key]['prime_price'] = DateUtils::formatData($val['prime_price']) . '';
            $recharges[$key]['present_price'] = DateUtils::formatData($val['present_price']) . '';
            $recharges[$key]['prime_price_num'] = $val['prime_price'];
            $recharges[$key]['present_price_num'] = $val['present_price'];
            $recharges[$key]['type_nid'] = UserVipStrategy::getOrderNidByVipNid($val['type_nid']);
        }

        return $recharges ? $recharges : [];
    }

    /**
     * 不通类型下的过期时间
     *
     * @param $message
     * @param $nid
     * @return array
     */
    public static function getDiffVipPeriodOfValidity($message, $nid)
    {
        if (!empty($message) && $message['status'] == 1) {
            $endTime = strtotime($message['end_time']);
            if ($endTime > time()) {
                $timeStamp = $endTime + (PaymentFactory::getSubVipTimeByNid($nid) * 24 * 60 * 60);
                $data['time'] = date('Y-m-d H:i:s', $timeStamp);
            } else {
                $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
            }
        } else {
            $data['time'] = date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($nid));
        }

        return $data ? $data : [];
    }

    /**
     * 特权统计数据处理
     *
     * @param $data
     * @param $user
     * @param $privilegeInfo
     * @return array
     */
    public static function getOauthPrivilegeDatas($data, $user, $privilegeInfo)
    {
        $data['user']['username'] = isset($user['user']['username']) ? $user['user']['username'] : '';
        $data['user']['mobile'] = isset($user['user']['mobile']) ? $user['user']['mobile'] : '';
        $data['user']['sex'] = isset($user['profile']['sex']) ? $user['profile']['sex'] : '';
        $data['user']['real_name'] = isset($user['profile']['real_name']) ? $user['profile']['real_name'] : '';
        $data['user']['idcard'] = isset($user['profile']['identity_card']) ? $user['profile']['identity_card'] : '';

        //工具数据
        $data['privilege']['privilege_id'] = isset($privilegeInfo['id']) ? $privilegeInfo['id'] : '';
        $data['privilege']['type_nid'] = isset($privilegeInfo['type_nid']) ? $privilegeInfo['type_nid'] : '';
        $data['privilege']['name'] = isset($privilegeInfo['name']) ? $privilegeInfo['name'] : '';
        $data['privilege']['subtitle'] = isset($privilegeInfo['subtitle']) ? $privilegeInfo['subtitle'] : '';
        $data['privilege']['url'] = isset($privilegeInfo['url']) ? $privilegeInfo['url'] : '';
        $data['privilege']['is_abut'] = isset($privilegeInfo['is_abut']) ? $privilegeInfo['is_abut'] : '';

        return $data ? $data : [];
    }

    /**
     * 会员唯一标识与订单唯一标识的转化
     *
     * @param string $vipNid
     * @return string
     */
    public static function getOrderNidByVipNid($vipNid = '')
    {
        switch ($vipNid) {
            case UserVipConstant::VIP_ANNUAL_MEMBER:
                //年度会员
                $orderNid = UserVipConstant::ORDER_VIP_ANNUAL_MEMBER;
                break;
            case UserVipConstant::VIP_QUARTERLY_MEMBER:
                //季度会员
                $orderNid = UserVipConstant::ORDER_VIP_QUARTERLY_MEMBER;
                break;
            case UserVipConstant::VIP_MONTHLY_MEMBER:
                //月度会员
                $orderNid = UserVipConstant::ORDER_VIP_MONTHLY_MEMBER;
                break;
            default:
                $orderNid = UserVipConstant::ORDER_VIP_ANNUAL_MEMBER;;
        }

        return $orderNid ? $orderNid : '';
    }

    /**
     * 支付金额&描述
     *
     * @param array $params
     * @return array
     */
    public static function getHuijuOtherParamsByParam($params = [])
    {
        return [
            //支付金额
            'amount' => sprintf("%.2f", UserVipFactory::getReVipAmountByNid($params['subtypeNid'])),
            'productname' => UserVipConstant::ORDER_DEALER_NAME . ' - ' . UserVipConstant::ORDER_PRODUCT_NAME,
            'productdesc' => UserVipConstant::ORDER_DESC,
            'orderNo' => $params['order_id'],
            'url_params' => json_encode(['type' => $params['type'], 'vip_type' => $params['subtypeNid']]),
        ];
    }
}