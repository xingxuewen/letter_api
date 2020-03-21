<?php
/**
 * Created by PhpStorm.
 * User: zengqiang
 * Date: 17-10-25
 * Time: 下午8:36
 */

namespace App\Models\Factory;

use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\Banks;
use App\Models\Orm\huijubanksType;
use App\Models\Orm\UserBanks;
use App\Models\Orm\UserRealname;
use App\Services\Core\Store\Qiniu\QiniuService;
use function FastRoute\TestFixtures\empty_options_cached;

/**
 * Class UserBankCardFactory
 * @package App\Models\Factory
 * 绑定银行卡
 */
class UserBankCardFactory extends AbsModelFactory
{
    /**
     * 绑定银行列表
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param $data
     * @return array
     */
    public static function fetchUserbanks($data)
    {
        $pageSize = $data['pageSize'];
        $pageNum = $data['pageNum'];

        $query = UserBanks::select(['id', 'user_id', 'bank_id', 'account', 'card_default'])
            ->where([
                'user_id' => $data['userId'],
                'card_type' => $data['cardType'],
                'card_use' => 1,
                'status' => 0,
            ]);

        //储蓄卡按默认在前
        if ($data['cardType'] == 1) {
            $query->orderBy('card_default', 'desc');
        }
        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $userbanks = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $params['list'] = $userbanks;
        $params['pageCount'] = $countPage ? $countPage : 0;

        return $params ? $params : [];
    }


    public static function fetchUserbanks_isDefault($userid)
    {
        $res = UserBanks::select(['id'])
            ->where([
                'user_id' => $userid,
                'card_use' => 1,
                'status' => 0,
                'card_default'=>"1",
            ])->first();
        return empty($res)?1:0;
    }

    // 通过银行卡ID 和用户ID判断该卡是否存在
    public static function fetchUserbanks_isAva($bankcardid,$userid)
    {
        $res = UserBanks::select(['id'])
            ->where([
                'user_id' => $userid,
                'card_use' => 1,
                'id'=>$bankcardid,
                'status' => 0,
            ])->first();
        return empty($res)?0:1;
    }

    /**
     * 获取银行信息，用户信息
     * @param array $params
     * @return array
     */
    public static function fetchUserbanksinfo($params = [])
    {
        $data = [];
        foreach ($params as $key => $val) {
            $bankinfo = BankFactory::fetchBankinfoById($val['bank_id']);
            $data[$key]['user_bank_id'] = $val['id'];
            $data[$key]['bankname'] = empty($bankinfo['sname']) ? $bankinfo['name'] : $bankinfo['sname'];
            $data[$key]['banklogo'] = QiniuService::getImgs($bankinfo['litpic']);
            $data[$key]['account'] = $val['account'];
            $realname = UserIdentityFactory::fetchRealnameById($val['user_id']);
            $data[$key]['realname'] = $realname;
            $data[$key]['card_default'] = $val['card_default'];
        }

        return $data ? $data : [];
    }

    /**
     * 获取卡片信息
     * @param $id
     * @param $userid
     * @return array
     */
    public static function getBankCardInfo($id, $userid)
    {
        $res = UserBanks::select()
            ->where(['id' => $id, 'user_id' => $userid, 'card_use' => 1])
            ->where('status', '!=', 9)
            ->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 添加银行卡，天创4要素验证
     * @param $params
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @return bool
     */
    public static function createOrUpdateUserBank($params)
    {
        $userbanks = UserBanks::select(['id'])->where(['status' => 0,
            'user_id' => $params['userId'], 'card_use' => 1, 'account' => $params['account']])
            ->first();
        if (!$userbanks) {
            //入库
            $userbanks = new UserBanks();
            $userbanks->created_ip = Utils::ipAddress();
            $userbanks->created_at = date('Y-m-d H:i:s');
        }
        $userbanks->user_id = $params['userId'];
        $userbanks->bank_id = $params['bankId'];
        $userbanks->account = $params['account'];
        $userbanks->bank_name = $params['bankname'];
        $userbanks->card_type = $params['cardtype'];
        $userbanks->card_default = $params['card_default'];
        $userbanks->card_use = 1;
        $userbanks->card_last_status = $params['card_last_status'];
        $userbanks->card_mobile = $params['mobile'];
        $userbanks->updated_ip = Utils::ipAddress();
        $userbanks->updated_at = date('Y-m-d H:i:s');
        $userbanks->save();

        $data['id'] = $userbanks->id;
        return $data;
    }

    public static function createOrUpdateUserBank_new($params)
    {
        $userbanks = UserBanks::select(['id'])->where(['status' => 0,
            'user_id' => $params['userId'], 'card_use' => 1, 'account' => $params['account']])
            ->first();
        if (!$userbanks) {
            //入库
            $userbanks = new UserBanks();
            $userbanks->created_ip = Utils::ipAddress();
            $userbanks->created_at = date('Y-m-d H:i:s');
        }
        $userbanks->user_id = $params['userId'];
        $userbanks->bank_id = $params['bankId'];
        $userbanks->account = $params['account'];
        $userbanks->bank_name = $params['bankname'];
        $userbanks->card_type = $params['cardtype'];
        $userbanks->card_default = $params['card_default'];
        $userbanks->card_use = 1;
        $userbanks->card_last_status = $params['card_last_status'];
        $userbanks->card_mobile = $params['mobile'];
        $userbanks->cvv2 = $params['cvv2'];
        $userbanks->avatime = $params['avatime'];
        $userbanks->updated_ip = Utils::ipAddress();
        $userbanks->updated_at = date('Y-m-d H:i:s');
        $userbanks->save();

        $data['id'] = $userbanks->id;
        return $data;
    }

    public static function createOrUpdateUserBank_new_bangcard($params)
    {
        $userbanks = UserBanks::select(['id'])->where(['status' => 0,
            'user_id' => $params['userId'], 'card_use' => 1, 'account' => $params['account']])
            ->first();
        if (!$userbanks) {
            //入库
            $userbanks = new UserBanks();
            $userbanks->created_ip = Utils::ipAddress();
            $userbanks->created_at = date('Y-m-d H:i:s');
        }
        $userbanks->user_id = $params['userId'];
        $userbanks->bank_id = $params['bankId'];
        $userbanks->account = $params['account'];
        $userbanks->bank_name = $params['bankname'];
        $userbanks->card_type = $params['cardtype'];
        $userbanks->card_default =$params['card_default'];
        $userbanks->card_use = 1;
        $userbanks->card_last_status = $params['card_last_status'];
        $userbanks->card_mobile = $params['mobile'];
        $userbanks->cvv2 = $params['cvv2'];
        $userbanks->avatime = $params['avatime'];
        $userbanks->updated_ip = Utils::ipAddress();
        $userbanks->updated_at = date('Y-m-d H:i:s');
        $userbanks->save();

        $data['id'] = $userbanks->id;
        return $data;
    }

    //  绑卡支付  成功 则 认证成功
    // by xuyj  v3.2.3
    public static function insertRealNameByBangcard($userlname,$idcard,$userid){

        $realname = UserRealName::select(['id'])->where(['user_id' =>$userid])
            ->first();
        if (!$realname) {
            //入库
            $realname = new UserRealName();

            $realname->created_ip = Utils::ipAddress();
            $realname->created_at = date('Y-m-d H:i:s');
            $realname->user_id = $userid;

            $realname->realname = $userlname;

            $realname->certificate_no = $idcard;
            $realname->certificate_backup = $idcard;
            $realname->sex = "1";
            $realname->status="2";
            $realname->save();
        }

        return $realname;
    }

    /**
     * 通过银行简称获取银行信息
     * @param $bankcode
     * @return array
     */
    public static function getBankInfoByBankcode($bankcode)
    {
        $bankinfo = Banks::select('id', 'litpic', 'sname', 'name')->where(['nid' => $bankcode, 'status' => 0,])->first();
        return $bankinfo ? $bankinfo->toArray() : [];
    }


    /**
     * 获取默认银行卡信息
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param $userid
     * @return mixed|string
     */
    public static function getDefaultBankCardIdById($userId)
    {
        $res = UserBanks::select('id')
            ->where(['user_id' => $userId, 'status' => 0, 'card_type' => 1, 'card_use' => 1, 'card_default' => 1])->pluck('id');
        return $res ? $res->toArray() : [];
    }

    /**
     * 储蓄卡是否存在
     * @param $userid
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @return array
     */
    public static function fetchCarddefaultById($userid)
    {
        $res = UserBanks::select('id')
            ->where(['user_id' => $userid, 'status' => 0, 'card_type' => 1, 'card_use' => 1])
            ->first();
        return $res ? $res->toArray() : [];
    }

    /**
     * 查询上次支付银行卡是否存在
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @card_last_status  最后使用支付的银行卡状态 【0未使用，1已使用】
     * @param $userId
     * @return array
     */
    public static function fetchCardLastPayById($userId)
    {
        $res = UserBanks::select()
            ->where(['user_id' => $userId, 'status' => 0, 'card_use' => 1, 'card_last_status' => 1])
            ->first();
        return $res ? $res->toArray() : [];
    }


    /**
     * 查询上次支付银行卡是否存在
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @card_last_status  最后使用支付的银行卡状态 【0未使用，1已使用】
     * @param $userId
     * @return array
     */
    public static function fetchCardLastPayById_new($userId)
    {
        $res = UserBanks::select()
            ->where(['user_id' => $userId, 'status' => 0, 'card_use' => 1, 'card_last_status' => 1,'hjcard_default'=>'1'])
            ->first();

        if(empty($res)){
            logInfo("fetchUserBankInfoById_new ");
            //     $res = UserBanks::select()
            //         ->where(['user_id' => $params['userId'], 'id' => $params['id']])
            //        ->where(['status' => 0, 'card_use' => 1])
            //         ->first();
            // $query = UserBanks::select(['id', 'bank_id', 'account', 'bank_name', 'sbank_name', 'card_type', 'card_default', 'card_use', 'card_last_status', 'card_mobile', 'status','huiju_paycount','cvv2','avatime','card_mobile'])
            $query = UserBanks::select()
                ->where(['card_use' => 1, 'user_id' => $userId, 'status' => 0])
                ->whereIn('card_type', [1, 2]);
            //排序 储蓄卡在上，默认储蓄卡在前，后添加卡在前
            $query->orderBy('card_type', 'asc')
                ->orderBy('card_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc');
            $arr = $query->get()->toArray();

            foreach ($arr as $key => $val) {
                if(!empty($val['bank_name'])){
                    logInfo("333333333333333333", $val);
                    $resBk = UserBankCardFactory::fetchCurCardisInHuiJu($val['bank_name']);
                    if(!empty($resBk)){
                        $res = $val;
                        logInfo("4444444444");
                        goto funcexit;
                    }
                }
            }
        }
        funcexit:
        logInfo("333333333333333333".\GuzzleHttp\json_encode($res));
        return $res ? $res : [];
    }

    /**
     * 设置一张支付卡
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @card_last_status  最后使用支付的银行卡状态 【0未使用，1已使用】
     * @param $userId
     * @return bool
     */
    public static function updateCardLastPayStatus($userId)
    {
        $query = UserBanks::select(['id'])
            ->where(['card_use' => 1, 'user_id' => $userId, 'status' => 0])
            ->whereIn('card_type', [1, 2]);
        //排序 储蓄卡在上，默认储蓄卡在前，后添加卡在前
        $query->orderBy('card_type', 'asc')
            ->orderBy('card_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        $status = $query->limit(1)->update(['card_last_status' => 1]);

        return $status;
    }

    /**
     * 根据时间倒叙排列储蓄卡 选出最近一张储蓄卡id
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param $userId
     * @return array
     */
    public static function fetchCarddefaultIdByTime($userId)
    {
        $default = UserBanks::select(['id'])
            ->where(['user_id' => $userId, 'status' => 0, 'card_type' => 1, 'card_use' => 1])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();

        return $default ? $default->toArray() : [];
    }

    /**
     * 删卡
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @card_last_status  最后使用支付的银行卡状态 【0未使用，1已使用】
     * @param array $params
     * @return bool
     */
    public static function deleteUserBankById($params = [])
    {
        $res = UserBanks::where([
            'id' => $params['userbankId'],
            'user_id' => $params['userId'],
            'card_type' => $params['cardType'],
            'card_use' => 1,
        ])
            ->update(['status' => 9, 'card_default' => 0, 'card_last_status' => 0]);

        return $res;
    }

    // 更新信用卡信息 或者 储蓄卡信息
    public static function updateCardInfo($params = []){
        $res = UserBanks::where([
            'account' => $params['account'],
        ])
            ->update(['cvv2' => $params['cvv2'], 'avatime' => $params['avatime']]);

        return $res;
    }

    /**
     * 设置默认卡储蓄卡
     * @param array $params
     * @return bool
     */
    public static function setDefaultById($params = [])
    {
        return UserBanks::where([
            'id' => $params['userbankId'],
            'card_type' => 1,
            'card_use' => 1,
            'status' => 0,
            'user_id' => $params['userId'],
        ])
            ->update(['card_default' => 1]);
    }


    /**
     * 取消默认卡储蓄卡
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param array $data
     * @return bool
     */
    public static function deleteDefaultById($data = [])
    {
        return UserBanks::where([
            'user_id' => $data['userId'],
            'card_type' => 1,
            'card_use' => 1,
            'status' => 0,
        ])->whereIn('id', $data['ids'])
            ->update(['card_default' => 0]);
    }


    /**
     * 获取最新添加的非默认储蓄卡
     * @param $userid
     * @return array
     */
    public static function getLastestNotDefaultCard($userid)
    {
        $where = [
            'card_type' => 1,
            'card_use' => 1,
            'card_default' => 0,
            'user_id' => $userid,
        ];
        $res = UserBanks::select('id')->where($where)
            ->orderByDesc('created_at')->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 获取最后使用的银行卡信息
     *
     * @param $userId
     * @return mixed
     */
    public static function getDefaultBankCard($userId)
    {
        $res = UserBanks::where([
            'card_use' => 1,
            'user_id' => $userId,
            'card_last_status' => 1,
            'status' => 0,
        ])->first(['id', 'bank_id', 'account', 'bank_name', 'sbank_name', 'card_type', 'card_default', 'card_use', 'card_last_status', 'card_mobile', 'status']);

        return $res ? $res : [];
    }

    /**
     * 根据用户id与绑定银行卡id获取绑定银行信息
     * @param array $params
     * @return array
     */
    public static function fetchUserBankInfoById($params = [])
    {
        $res = UserBanks::select()
            ->where(['user_id' => $params['userId'], 'id' => $params['id']])
            ->where(['status' => 0, 'card_use' => 1])
            ->first();
        return $res ? $res->toArray() : [];
    }

    // 判断当前储蓄卡是否已经绑定
    // by xuyj v3.2.3
    public static function isBankCardBang_cxk($cardnum)
    {
        $res = UserBanks::select()
            ->where(['account'=>$cardnum])
            ->where('huiju_paycount',">","0")
            ->where('status',"!=","9")
            ->first();
        return $res ? $res->toArray() : [];
    }


    // 判断当前信用卡是否已经绑定
    // by xuyj v3.2.3
    public static function isBankCardBang_creditcard($cardnum)
    {
        $res = UserBanks::select()
            ->where(['account'=>$cardnum])
            ->where('huiju_paycount',">","0")
            ->where('cvv2','!=','0')
            ->where('avatime','!=','0')
            ->where('huijusignid','!=','0')
            ->where('status','!=','9')
            ->first();
        return $res ? $res->toArray() : [];
    }


    public static function getRealnamebyUserid($userid)
    {
        logInfo("ididididididididididid");
        $user = UserRealName::select()->where(['user_id'=>$userid])->first();

        return $user ? $user->toArray():[];
    }

    /**
     * 根据用户id与绑定银行卡id获取绑定银行信息
     * @param array $params
     * @return array
     * by xuyj 2019-02-21
     */
    public static function fetchUserBankInfoById_new($params = [])
    {
        $res = UserBanks::select()
            ->where(['user_id' => $params['userId'], 'id' => $params['id']])
            ->where(['status' => 0, 'card_use' => 1])
            ->where(['hjcard_default'=>1])
            ->first();
        logInfo("00000000000000000000000000000000000");
        logInfo(json_encode($res));
        if(empty($res)){
            logInfo("fetchUserBankInfoById_new ");
            $query = UserBanks::select()
                ->where(['card_use' => 1, 'user_id' => $params['userId'], 'status' => 0])
                ->whereIn('card_type', [1, 2]);
            //排序 储蓄卡在上，默认储蓄卡在前，后添加卡在前
            $query->orderBy('card_type', 'asc')
                ->orderBy('card_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc');
            $arr = $query->get()->toArray();

            foreach ($arr as $key => $val) {
                if(!empty($val['bank_name'])){
                    logInfo("333333333333333333".\GuzzleHttp\json_encode($val));
                    $resBk = UserBankCardFactory::fetchCurCardisInHuiJu($val['bank_name']);
                    if(!empty($resBk)){
                        $res = $val;
                        logInfo("333333333333333333".\GuzzleHttp\json_encode($val));
                        logInfo("333333333333333333".\GuzzleHttp\json_encode($res));
                        goto funcexit;
                    }
                }
            }
        }
        funcexit:
        logInfo("333333333333333333".\GuzzleHttp\json_encode($res));
        return $res ? $res : [];
    }


    /**
     * 获取使用卡片的列表
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param array $data
     * @return array
     */
    public static function getUsedCardList($data)
    {
        $userid = $data['userId'];
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);

        $query = UserBanks::select(['id', 'bank_id', 'account', 'bank_name', 'sbank_name', 'card_type', 'card_default', 'card_use', 'card_last_status', 'card_mobile', 'status'])
            ->where(['card_use' => 1, 'user_id' => $userid, 'status' => 0])
            ->whereIn('card_type', [1, 2]);
        //排序 储蓄卡在上，默认储蓄卡在前，后添加卡在前
        $query->orderBy('card_type', 'asc')
            ->orderBy('card_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;

        $arr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $banks['list'] = $arr;
        $banks['pageCount'] = $countPage ? $countPage : 0;

        return $banks;
    }


    /**
     * 获取使用卡片的列表
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param array $data
     * @return array
     *  by xuyj v3.2.3
     */
    public static function getUsedCardList_new($data)
    {
        $userid = $data['userId'];
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);

        $query = UserBanks::select(['id', 'bank_id', 'account', 'bank_name', 'sbank_name', 'card_type', 'card_default', 'card_use', 'card_last_status', 'card_mobile', 'status','huiju_paycount','cvv2','avatime','card_mobile'])
            ->where(['card_use' => 1, 'user_id' => $userid, 'status' => 0])
            ->whereIn('card_type', [1, 2]);
        //排序 储蓄卡在上，默认储蓄卡在前，后添加卡在前
        $query->orderBy('card_type', 'asc')
            ->orderBy('card_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;

        $arr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $banks['list'] = $arr;
        $banks['pageCount'] = $countPage ? $countPage : 0;

        return $banks;
    }

    /**
     * 获取logo链接
     *
     * @param int $bankId 银行ID
     * @return mixed
     */
    public static function getBankLogo($bankId)
    {
        return Banks::where(['id' => $bankId])->value('litpic');
    }

    /**
     * 设置最近一次支付使用的银行卡
     * @param $bankcard_id
     * @param $userid
     */
    public static function setLastestUsedCard($bankcard_id, $userid)
    {

        UserBanks::where(['user_id' => $userid,])->where('id', '!=', $bankcard_id)
            ->update(['card_last_status' => 0]);

        UserBanks::where(['user_id' => $userid,])->where('id', '=', $bankcard_id)
            ->update(['card_last_status' => 1]);
    }

    /**
     * 用户储蓄卡总张数
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param $data
     * @return int
     */
    public static function fetchSavingCountById($data)
    {
        $count = UserBanks::select(['id'])
            ->where([
                'user_id' => $data['userId'],
                'card_type' => $data['cardType'],
                'card_use' => 1,
                'status' => 0,
            ])->count();

        return $count ? $count : 0;
    }

    /**
     * 用户绑定卡总张数
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param $data
     * @return int
     */
    public static function fetchUserBanksCount($data)
    {
        $count = UserBanks::select(['id'])
            ->where([
                'user_id' => $data['userId'],
                'card_use' => 1,
                'status' => 0,
            ])->count();

        return $count ? $count : 0;
    }

    /**
     * 查询银行卡支付状态
     * @card_use  使用状态【0信用资料，1认证银行】
     * @card_last_status 最后使用支付的银行卡状态 【0未使用，1已使用】
     * @return array|\Illuminate\Support\Collection
     */
    public static function fetchLastBankIdsByStatus($userId)
    {
        $ids = UserBanks::select(['id'])
            ->where(['user_id' => $userId])
            ->where(['card_last_status' => 1, 'card_use' => 1])
            ->pluck('id');

        return $ids ? $ids->toArray() : [];
    }

    /**
     * 查询以前是否有银行卡支付
     * @param array $params
     * @return array
     */
    public static function fetchLastPaymentById($params = [])
    {
        $ids = UserBanks::select(['id'])
            ->where(['card_last_status' => 1])
            ->where(['user_id' => $params['userId'], 'card_use' => 1])
            ->pluck('id');

        return $ids ? $ids->toArray() : [];
    }

    /**
     * 取消上次支付状态
     * @param array $params
     * @return bool
     */
    public static function deleteCardLastStatusByIds($params = [])
    {
        return UserBanks::where(['card_use' => 1])
            ->where(['user_id' => $params['userId']])
            ->whereIn('id', $params['ids'])
            ->update(['card_last_status' => 0]);
    }

    /**
     * 修改支付卡片状态
     * @param $id
     * @return bool
     */
    public static function updateCardLastStatusById($params = [])
    {
        return UserBanks::where(['id' => $params['userbankId'], 'user_id' => $params
        ['userId'], 'status' => 0, 'card_use' => 1])
            ->update(['card_last_status' => 1]);
    }

    /**
     * 判断是否已添加过该银行卡
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param array $params
     * @return array
     */
    public static function fetchUserBankByAccount($params = [])
    {
        $userbank = UserBanks::select(['id'])
            ->where([
                'user_id' => $params['userId'],
                'account' => $params['account'],
                'card_use' => 1,
                'status' => 0,
            ])->first();

        return $userbank ? $userbank->toArray() : [];
    }

    // 保存 / 更新 汇聚支付的短信签约码
    public static function updateHuiJuPaySmsSignCodeBySignCode($userid,$bankcard,$mobile,$sign)
    {
        return UserBanks::where([
            'user_id' => $userid,
            'account' => $bankcard,
            'card_mobile' =>$mobile,
        ])->update(['huijusignid'=>$sign,'huiju_paycount'=>"1"]);
           // ->update(['card_last_status' => "1",'huijusignid'=>$sign,'huiju_paycount'=>"1"]);

    }

    /**
     * 判断当前银行卡是否是汇聚支付所支持的
     * @param array $params
     * @return array
     *  by xuyj 2019-02-21 v3.2.3
     */
    public static function fetchCurCardisInHuiJu($bankname)
    {
        $userbank = huijubanksType::select(['bankname','bkcolor'])
            ->where([
                'bankname' => $bankname,
            ])->first();

        return $userbank ? $userbank->toArray() : [];
    }

    /**
     * 判断是否实名认证通过
     *  sd_user_realname . status >2 为实名认证通过
     * @param array $params
     * @return array
     *  by xuyj 2019-02-21 v3.2.3
     */
    public static function fetchIsRealnameOK($userid)
    {
        $userbank = UserRealname::select(['status'])
            ->where([
                'user_id' => $userid ,
            ])->first();

        return $userbank ? $userbank->toArray() : [];
    }

}