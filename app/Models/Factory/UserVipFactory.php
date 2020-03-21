<?php
/**
 * Created by PhpStorm.
 * User: zengqiang
 * Date: 17-10-26
 * Time: 下午8:42
 */

namespace App\Models\Factory;

use App\Constants\LieXiongConstant;
use App\Constants\UserVipConstant;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataUserVipPrivilegeLog;
use App\Models\Orm\PlatformProductVip;
use App\Models\Orm\SystemConfig;
use App\Models\Orm\User;
use App\Helpers\UserAgent;
use App\Models\Orm\UserInfo;
use App\Models\Orm\UserVip;
use App\Models\Orm\UserVipPrivilege;
use App\Models\Orm\UserVipPrivilegeRelation;
use App\Models\Orm\UserVipPrivilegeType;
use App\Models\Orm\UserVipSubtype;
use App\Models\Orm\UserVipType;
use App\Strategies\UserVipStrategy;
use Illuminate\Support\Facades\DB;

class UserVipFactory extends AbsModelFactory
{
    /**
     * 获取vip特权信息
     *
     * @param $privilegeId
     * @return array
     */
    public static function getVipPrivilegeInfo($privilegeId, $priTypeId)
    {
        $res = UserVipPrivilege::where(['id' => $privilegeId, 'status' => 1, 'is_desc' => 1, 'type_id' => $priTypeId])->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 获取特权id集合
     *
     * @param $vipTypeId
     * @return array
     */
    public static function getVipPrivilegeIds($vipTypeId)
    {
        $pids = UserVipPrivilegeRelation::where(['type_id' => $vipTypeId, 'status' => 1])->pluck('privilege_id')->toArray();

        return $pids ? $pids : [];
    }

    /**
     * 更新插入vip信息
     *
     * @param array $data
     * @return mixed
     */
    public static function createVipInfo($data = [])
    {
        $now = date('Y-m-d H:i:s');

        $message = UserVip::select()->where(['user_id' => $data['user_id']])->first();

        if (empty($message)) {
            $message = new UserVip();
            $message->status = 4;
            $message->created_ip = Utils::ipAddress();
            $message->created_at = date('Y-m-d H:i:s');
        } else {
            //判断是否是会员
            if ($message['status'] == 1 && $message['end_time'] < $now) {
                $message->end_time = '1970-01-01 00:00:00';
                $message->status = 3;
            }
        }

        $message->user_id = $data['user_id'];
        $message->vip_no = $data['vip_no'];//会员编号
        $message->vip_type = $data['vip_type'];

        if (!empty($data['subtype_id'])) {
            $message->subtype_id = $data['subtype_id'];
        } else {
            //子会员id  默认都是按年会员
            $message->subtype_id = UserVipFactory::getSubtypeIdByNid(UserVipConstant::VIP_ANNUAL_MEMBER);
        }

        $message->start_time = date('Y-m-d H:i:s');
        $message->updated_ip = Utils::ipAddress();
        $message->updated_at = date('Y-m-d H:i:s');

        return $message->save();
    }

    /**
     * 更新插入vip信息
     *
     * @param array $data
     * @return mixed
     */
    public static function createVipInfoForUnlockLogin($data = [])
    {
        $now = date('Y-m-d H:i:s');

        $message = UserVip::select()->where(['user_id' => $data['user_id']])->first();

        if (empty($message)) {
            $message = new UserVip();

            $message->created_ip = Utils::ipAddress();
            $message->start_time = $now;
            $message->created_at = $now;
            $message->end_time = date('Y-m-d 23:59:59',strtotime("+30 day"));
        } else {
            //如果未过期
            if ($message['status'] == 1 && $message['end_time'] < $now) {
                $message->end_time = date('Y-m-d 23:59:59',strtotime("+30 day",strtotime($message['end_time'])));
            } else {
                $message->start_time = $now;
                $message->end_time = date('Y-m-d 23:59:59',strtotime("+30 day"));
            }
        }

        $message->status     = 1;
        $message->user_id    = $data['user_id'];
        $message->vip_no     = $data['vip_no'];
        $message->vip_type   = $data['vip_type'];
        $message->subtype_id = $data['subtype_id'];
        $message->updated_ip = Utils::ipAddress();
        $message->updated_at = $now;

        return $message->save();
    }

    /**
     * 获取会员信息
     *
     * @param $userId
     * @return array
     */
    public static function getUserVip($userId)
    {
        $time = date('Y-m-d H:i:s', time());
        $data = UserVip::where(['user_id' => $userId, 'status' => 1])
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->first();

        return $data ? $data : [];
    }

    /**
     * 获取会员价格
     *
     * @return int|mixed
     */
    public static function getVipAmount()
    {
        $amount = UserVipType::where(['type_nid' => UserVipConstant::VIP_TYPE_NID, 'status' => 1])->value('vip_consume');;

        return $amount ? $amount : 0;
    }

    /**
     * 根据vip类型,获取价格
     *
     * @param $typeNid
     * @return int
     */
    public static function getReVipAmount($typeNid)
    {
        $amount = UserVipType::where(['type_nid' => $typeNid, 'status' => 1])->value('vip_consume');;

        return $amount ? $amount : 0;
    }

    /**
     * 获取会员类型ID
     *
     * @return mixed|string
     */
    public static function getVipTypeId()
    {
        $id = UserVipType::where(['type_nid' => UserVipConstant::VIP_TYPE_NID, 'status' => 1])->value('id');

        return $id ? $id : "";
    }

    /**
     * 根据不同的vip类型获取id
     *
     * @param $typeNid
     * @return string
     */
    public static function getReVipTypeId($typeNid)
    {
        $id = UserVipType::where(['type_nid' => $typeNid, 'status' => 1])->value('id');

        return $id ? $id : "";
    }

    /**
     * 获取普通用户类型ID
     *
     * @return int
     */
    public static function getCommonTypeId()
    {
        $id = UserVipType::where(['type_nid' => UserVipConstant::VIP_TYPE_NID_VIP_COMMON, 'status' => 1])->value('id');

        return $id ? $id : 1;
    }

    /**
     * 获取统计数值
     *
     * @param $typeId
     * @return int
     */
    public static function getStatistics($typeId)
    {
        $data = PlatformProductVip::select(DB::raw('count(*) as user_count, vip_type_id, product_id'))
            ->where(['vip_type_id' => $typeId])->first()->toArray();

        return $data;
    }


    /**
     * 获取特权信息
     *
     * @param $id
     * @return array
     */
    public static function getPrivilege($id)
    {
        //'type_nid' => $nid,
        $data = UserVipPrivilege::where(['id' => $id, 'status' => 1])->first();

        return $data ? $data : [];
    }

    /**
     * 获取特权的ID
     *
     * @param $typeId
     * @return array
     */
    public static function getPrivilegeId($typeId)
    {
        $ids = UserVipPrivilegeRelation::select('privilege_id')->where(['type_id' => $typeId, 'status' => 1])->get()->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 获取user_vip个数
     *
     * @return mixed
     */
    public static function getUserVipCount()
    {
        return UserVip::count();
    }

    /**
     * 获取user_vip个数
     * 筛选条件:头像存在,用户名不是sd开头
     *
     * @return mixed
     */
    public static function getUserVipCountByPhoto()
    {
        return UserVip::from('sd_user_vip as vip')
            ->join('sd_user_info as info', 'vip.user_id', '=', 'info.user_id')
            ->where('info.user_photo', '<>', '')
            ->count();
    }

    /**
     * 获取user_vip限制的数据
     * 筛选条件:筛选头像靠前的
     *
     * @param int $limit
     * @return mixed
     */
    public static function getUserVipLimitByPhoto($limit = 10)
    {
        $data = UserVip::select('vip.user_id')->from('sd_user_vip as vip')
            ->join('sd_user_info as info', 'vip.user_id', '=', 'info.user_id')
            ->where('info.user_photo', '<>', '')
            ->orderBy('info.user_photo', 'desc')
            ->orderByRaw('RAND()')
            ->distinct()
            ->limit($limit)->get()->toArray();

        return $data;
    }

    /**
     * 获取user_vip限制的数据
     *
     * @param int $limit
     * @return mixed
     */
    public static function getUserVipLimit($limit = 10)
    {
        //->orderBy('id', $desc)
        $data = UserVip::select('user_id')->orderByRaw('RAND()')->limit($limit)->get()->toArray();

        return $data;
    }

    /**
     * 随机获取用户
     *
     * @param int $limit
     * @return mixed
     */
    public static function getUserLimit($limit = 10)
    {
        $distance = 1000;
        $uid = User::orderBy('sd_user_id', 'desc')->first();
        $from = $uid['sd_user_id'] - $distance;

        $data = User::select('sd_user_id as user_id')->whereBetween('sd_user_id', [$from, $uid['sd_user_id']])->orderByRaw('RAND()')->limit($limit)->get()->toArray();

        return $data;
    }


    /**
     * 随机获取用户
     * 修改头像优先
     *
     * @param int $limit
     * @return mixed
     */
    public static function getUserLimitByPhoto($limit = 10)
    {
        $distance = 1000;
        $uid = User::orderBy('sd_user_id', 'desc')->first();
        $from = $uid['sd_user_id'] - $distance;

        $data = User::select('sd_user_id as user_id')->from('sd_user_auth as auth')
            ->join('sd_user_info as info', 'auth.sd_user_id', '=', 'info.user_id')
            ->whereBetween('auth.sd_user_id', [$from, $uid['sd_user_id']])
            ->orderBy('info.user_photo', 'desc')
            ->orderByRaw('RAND()')
            ->distinct()
            ->limit($limit)->get()->toArray();

        return $data;
    }

    /**
     * 获取用户表：昵称,手机号
     *
     * @param $userId
     * @return array
     */
    public static function getUser($userId)
    {
        $data = User::select(['username', 'mobile'])->where(['sd_user_id' => $userId])->first();

        return $data ? $data : [];
    }

    /**
     * 获取用户头像
     *
     * @param $userId
     * @return string
     */
    public static function getUserInfo($userId)
    {
        $data = UserInfo::where(['user_id' => $userId])->value('user_photo');

        return $data ? $data : "";
    }

    /**
     * 根据vip_type_nid 查找对应id
     * @param string $param
     * @return string
     */
    public static function fetchIdByVipType($param = '')
    {
        $id = UserVipType::where(['type_nid' => $param, 'status' => 1])->value('id');

        return $id ? $id : "";
    }

    /**
     * 获取会员信息【包括非正常状态】
     * @param $userid
     * @return array
     */
    public static function getInfo($userid)
    {
        $where = [
            'user_id' => $userid,
            'vip_type' => UserVipConstant::VIP_TYPE,
        ];

        $res = UserVip::select('*')->where($where)->first();
        return $res ? $res->toArray() : [];
    }


    /**
     * 获取会员VIP信息
     * @param $userid
     * @return array
     */
    public static function getVIPInfo($userid, $vipTypeId)
    {
        $now = date('Y-m-d H:i:s');
        $where = [
            'user_id' => $userid,
            'status' => 1,
            'vip_type' => $vipTypeId,
        ];

        $res = UserVip::select('*')->where($where)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->first();

        return $res ? $res->toArray() : [];
    }


    /**
     * 获取会员VIP信息
     * @param $userid
     * @return array
     */
    public static function getVIPInfoByUserId($userid)
    {
        $now = date('Y-m-d H:i:s');
        $where = [
            'user_id' => $userid,
            'status' => 1,
        ];

        $res = UserVip::select('*')->where($where)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 获取会员的最后的ID
     *
     * @return int|mixed
     */
    public static function getVipLastId()
    {
        $id = UserVip::orderBy('id', 'desc')->value('id');

        return $id ? $id : 1;
    }

    /**
     * 获取用户viptype主键id
     * @param $userId
     * @return int
     */
    public static function fetchUserVipToTypeByUserId($userId)
    {
        $now = date('Y-m-d H:i:s');
        $where = [
            'user_id' => $userId,
            'status' => 1,
        ];
        $res = UserVip::select('vip_type')->where($where)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->first();

        return $res ? $res->vip_type : 0;
    }

    /**
     * 获取vip type_nid 的值
     * @status 使用状态, 1 使用中, 0 未使用
     * @param $id
     * @return string
     */
    public static function fetchVipTypeById($id)
    {
        $type = UserVipType::select(['type_nid'])
            ->where(['id' => $id, 'status' => 1])
            ->first();

        return $type ? $type->type_nid : '';
    }

    /**
     * 验证用户是否是vip
     *
     * @param array $data
     * @return int
     */
    public static function checkIsVip($data = [])
    {
//        $vip_type = UserVipFactory::getReVipTypeId($data['vip_nid']);
//        $vipInfo = UserVipFactory::getVIPInfo($data['userId'], $vip_type);

        //验证用户是否是vip用户
        $vipInfo = UserVipFactory::getVIPInfoByUserId($data['userId']);

        return $vipInfo ? 1 : 0;
    }

    /**
     * 会员特权类型表
     * 根据nid查询对应的主键id
     *
     * @param string $nid
     * @return int
     */
    public static function fetchVipPrivilegeIdByNid($nid = '')
    {
        $id = UserVipPrivilegeType::select('id')
            ->where(['type_nid' => $nid, 'status' => 1])
            ->first();

        return $id ? $id->id : 0;
    }

    /**
     * 会员特权
     * 根据会员特权类型表区分版本
     * vip_privilege_upgrade
     *
     * @param array $params
     * @return array
     */
    public static function fetchVipPrivileges($params = [])
    {
        $privileges = UserVipPrivilege::select(['id', 'name', 'subtitle', 'type_nid', 'value', 'img_link', 'remark'])
            ->where(['type_id' => $params['priTypeId'], 'status' => 1])
            ->whereIn('id', $params['privilegeIds'])
            ->orderBy('position_sort', 'asc')
            ->get()->toArray();

        return $privileges ? $privileges : [];
    }

    /**
     * 会员中心 - 会员动态
     * 随机获取20个会员用户，循环展示
     *
     * @return array
     */
    public static function fetchMemberActivityInfo()
    {
        //随机获取20个vip用户，若vip用户数量不够，随机获取20个普通用户
        $userCount = UserVipConstant::DYNAMIC_USER_COUNT;
        $userids = UserVipFactory::fetchRandUserId($userCount);
        $userData = [];
        foreach ($userids as $uid) {
            $users = UserVipFactory::getUser($uid['user_id']);
            if (!empty($users)) {
                $message = UserVipStrategy::getRandMessage(UserVipConstant::DYNAMIC_MESSAGE);
                $userData[] = UserVipStrategy::getMemberActivityData($uid['user_id'], $message, $users);;
            }
        }

        $len = count($userData);
        if ($len < $userCount) {
            $limit = $userCount - $len;
            $ids = UserVipFactory::getUserLimit($limit);
            foreach ($ids as $id) {
                $user = UserVipFactory::getUser($id['user_id']);
                $message = UserVipStrategy::getRandMessage(UserVipConstant::DYNAMIC_MESSAGE);
                $userData[] = UserVipStrategy::getMemberActivityData($id['user_id'], $message, $user);
            }
        }

        return $userData;
    }

    /**
     * 获取随机20个用户ID
     *
     * @param int $count
     * @return mixed
     */
    public static function fetchRandUserId($count = 10)
    {
        //获取user_vip总数
        $userVips = UserVipFactory::getUserVipCountByPhoto();
//        logInfo('vip_count', ['data' => $userVips]);
        if ($userVips > $count) {
            $userIds = UserVipFactory::getUserVipLimitByPhoto($count);
        } else {
            //从user_auth中随机获取十个
            $userIds = UserVipFactory::getUserLimitByPhoto($count);
        }

        return $userIds;
    }

    /**
     * 会员动态+充值信息
     *
     * @param array $datas
     * @return mixed
     */
    public static function fetchUserVipAgain($datas = [])
    {
        $params = [];
        if (!empty($datas['userId'])) {
            $data = UserVipFactory::getUserVip($datas['userId']);
            if (!empty($data)) {
                $params['totalPriceTime'] = $data['end_time'];
                //date('Y', strtotime($data['end_time'])).'年'.date('m', strtotime($data['end_time'])).'月'.date('d', strtotime($data['end_time'])).'日到期';
                $params['loanVipCount'] = isset($datas['vip_diff_count']) ? $datas['vip_diff_count'] : 0;
                $params['creditCount'] = UserReportFactory::getUserReportCount($datas['userId']);
                $params['isVipUser'] = 1;
            }
        }

        //价格
        $arr['totalPrice'] = UserVipFactory::getVipAmount() . '/年';
        $arr['totalNoPrice'] = UserVipConstant::MEMBER_PRICE . '/年';
        //单纯显示价格
        $arr['totalPriceNum'] = UserVipFactory::getVipAmount() . '';
        $arr['totalNoPriceNum'] = UserVipConstant::MEMBER_PRICE . '';

        if (empty($params)) {
            $arr['isVipUser'] = 0;
            //vip产品数
            $arr['loanVipCount'] = 0;
            //闪信免费查个数
            $arr['creditPrice'] = '';
            //会员到期时间 是否显示立即续费 在一个月内显示 其余时间不显示
            $arr['totalPriceTime'] = '';
            //会员动态
            $arr['memberActivity'] = UserVipFactory::fetchMemberActivityInfo();
        } else {
            $arr['isVipUser'] = $params['isVipUser'];
            $arr['loanVipCount'] = $params['loanVipCount'];
            $arr['creditPrice'] = $params['creditCount'] * $datas['report_price'];
            //是否显示立即续费 在一个月内显示 其余时间不显示
            $isShow = UserVipStrategy::checkIsShowPriceTime($params['totalPriceTime']);
            if ($isShow == 1) $arr['totalPriceTime'] = DateUtils::formatDate($params['totalPriceTime']);
            else $arr['totalPriceTime'] = '';
            //是否显示立即续费 在一个月内显示 其余时间不显示 1显示 0不显示
            $arr['isShowPriceTime'] = empty($isShow) ? 0 : 1;
            $arr['memberActivity'] = [];
        }

        return $arr;
    }

    /**
     * 会员特权个数
     *
     * @param array $params
     * @return int
     */
    public static function fetchVipPrivilegeCount($params = [])
    {
        $count = UserVipPrivilege::select(['name', 'subtitle', 'type_nid', 'value', 'img_link', 'remark'])
            ->where(['type_id' => $params['priTypeId'], 'status' => 1])
            ->whereIn('id', $params['privilegeIds'])
            ->count();

        return $count ? $count : 0;
    }

    /**
     * 会员动态
     * 非会员显示会员动态
     * 会员显示轮播数据，会员的查询信用报告、产品的信息
     *
     * @param array $datas
     * @return mixed
     */
    public static function fetchUserVipsThirdUpgrade($datas = [])
    {
        $params = [];
        if (!empty($datas['userId'])) {
            $data = UserVipFactory::getUserVip($datas['userId']);
            if (!empty($data)) {
                $params['totalPriceTime'] = $data['end_time'];
                //date('Y', strtotime($data['end_time'])).'年'.date('m', strtotime($data['end_time'])).'月'.date('d', strtotime($data['end_time'])).'日到期';
                $params['loanVipCount'] = isset($datas['vip_diff_count']) ? $datas['vip_diff_count'] : 0;
                $params['creditCount'] = UserReportFactory::getUserReportCount($datas['userId']);
                $params['isVipUser'] = 1;
            }
        }

        if (isset($datas['isRecharge']) && $datas['isRecharge'] == 1) //续费 非会员数据
        {
            $params = [];
        }

        if (empty($params)) {
            //会员动态
            $arr['memberActivity'] = UserVipFactory::fetchMemberActivityInfo();
        } else {
            $arr['isVipUser'] = $params['isVipUser'];
            $arr['loanVipCount'] = $params['loanVipCount'];
            $arr['creditPrice'] = $params['creditCount'] * $datas['report_price'];
            //是否显示立即续费 在一个月内显示 其余时间不显示
            $isShow = UserVipStrategy::checkIsShowPriceTimeBySevenDays($params['totalPriceTime']);
            if ($isShow == 1) $arr['totalPriceTime'] = DateUtils::formatDate($params['totalPriceTime']);
            else $arr['totalPriceTime'] = '';
            //是否显示立即续费 在一个星期内显示 其余时间不显示 1显示 0不显示
            $arr['isShowPriceTime'] = empty($isShow) ? 0 : 1;
        }

        return $arr;
    }

    /**
     * 会员充值列表
     *
     * @param string $typeId
     * @return array
     */
    public static function fetchRechargesByTypeId($typeId = '')
    {
        $subType = UserVipSubtype::select(['id', 'name', 'subname', 'prime_price', 'present_price', 'is_recom', 'type_nid'])
            ->where(['type_id' => $typeId, 'status' => 1])
            ->get()->toArray();

        return $subType ? $subType : [];
    }

    /**
     * 会员子类型充值金额
     * 筛选条件：唯一标识、状态值
     *
     * @param string $typeNid
     * @return int
     */
    public static function getReVipAmountByNid($typeNid = '')
    {
        $amount = UserVipSubtype::where(['type_nid' => $typeNid, 'status' => 1])->value('present_price');;

        return $amount ? $amount : 0;
    }

    public static function getReVipAmountByid_new($typeNid = '')
    {
        $amount = UserVipSubtype::where(['id' => $typeNid, 'status' => 1])->value('present_price');;

        return $amount ? $amount : 0;
    }

    /**
     * 会员子类型主键id
     * 筛选条件：唯一标识、状态值
     *
     * @param string $typeNid
     * @return int
     */
    public static function getSubtypeIdByNid($typeNid = '')
    {
        $id = UserVipSubtype::where(['type_nid' => $typeNid, 'status' => 1])->value('id');;

        return $id ? $id : 0;
    }

    public static function getSubtypeIdByid_new($typeNid = '')
    {
        $id = UserVipSubtype::where(['type_nid' => $typeNid, 'status' => 1])->value('id');;

        return $id ? $id : 0;
    }

    /**
     * 子类型
     * 正常的过期时间
     *
     * @param string $nid
     * @return int
     */
    public static function getVipExpiredByNid($nid = '')
    {
        $expired = UserVipSubtype::where(['type_nid' => $nid, 'status' => 1])->value('period');
        return time() + $expired * 24 * 60 * 60;
    }

    /**
     * 子类型
     * 父类型id
     *
     * @param string $nid
     * @return string
     */
    public static function getTypeIdByNid($nid = '')
    {
        $typeId = UserVipSubtype::where(['type_nid' => $nid, 'status' => 1])->value('type_id');
        return $typeId ? $typeId : '';
    }

    public static function getTypeIdByid_new($nid = '')
    {
        $typeId = UserVipSubtype::where(['id' => $nid, 'status' => 1])->value('type_id');
        return $typeId ? $typeId : '';
    }


    /**
     * 子会员类型
     * 根据nid获取信息
     *
     * @param string $nid
     * @return array
     */
    public static function fetchSubtypeIdByNid($nid = '')
    {
        $subTypes = UserVipType::from('sd_user_vip_type as t')
            ->join('sd_user_vip_subtype as st', 't.id', '=', 'st.type_id')
            ->select(['t.id as vip_type', 'st.id'])
            ->where(['st.type_nid' => $nid, 'st.status' => 1, 't.status' => 1])
            ->first();
        return $subTypes ? $subTypes->toArray() : [];
    }

    /**
     * 会员表添加或修改会员信息
     *
     * @param array $data
     * @return bool
     */
    public static function createOrUpdateUserVip($data = [])
    {
        $now = date('Y-m-d H:i:s', time());
        $message = UserVip::select()
            //->where(['user_id' => $data['user_id'], 'vip_type' => $data['vip_type']])
            ->where(['user_id' => $data['user_id']])
            ->first();
        logInfo("createOrUpdateUserVip", $message);
        if (empty($message)) {
            $message = new UserVip();
            $message->status = 4;
            $message->created_ip = Utils::ipAddress();
            $message->created_at = date('Y-m-d H:i:s');
            $message->vip_type = $data['vip_type'];
            $message->subtype_id = $data['subtype_id'];
        } else {
            //判断是否是会员
            if ($message['status'] == 1 && $message['end_time'] < $now) {
                $message->end_time = '1970-01-01 00:00:00';
                $message->status = 3;
            }
//            else {
//                $message->end_time = date('Y-m-d H:i:s', time());
//            }
        }

        $message->user_id = $data['user_id'];
        $message->vip_no = $data['vip_no'];//会员编号
        $message->start_time = date('Y-m-d H:i:s');
        $message->updated_ip = Utils::ipAddress();
        $message->updated_at = date('Y-m-d H:i:s');
        logInfo(" createOrUpdateUserVip", $message);
        return $message->save();
    }

    /**
     * 特权点击流水统计
     *
     * @param array $datas
     * @return bool
     */
    public static function createDataUserVipPrivilegeLog($datas = [])
    {
        $log = new DataUserVipPrivilegeLog();
        $log->user_id = $datas['userId'];
        $log->username = $datas['user']['username'];
        $log->mobile = $datas['user']['mobile'];
        $log->privilege_id = $datas['privilege']['privilege_id'];
        $log->type_nid = $datas['privilege']['type_nid'];
        $log->name = $datas['privilege']['name'];
        $log->subtitle = $datas['privilege']['subtitle'];
        $log->url = $datas['url'];
        $log->channel_id = $datas['delivery']['id'];
        $log->channel_title = $datas['delivery']['title'];
        $log->channel_nid = $datas['delivery']['nid'];
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * 会员类型 vip_type  0不是会员，1平台会员，2烈熊会员
     *
     * @param $userId
     * @return int
     */
    public static function getUserVipType($userId)
    {
        $vipInfo = UserVipFactory::getVIPInfoByUserId($userId);

        if (empty($vipInfo)) {
            return 0;
        }

        if (in_array($vipInfo['subtype_id'], LieXiongConstant::VIP_TYPE)) {
            return 2;
        }

        return 1;
    }
}