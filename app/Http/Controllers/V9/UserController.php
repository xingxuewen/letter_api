<?php
namespace App\Http\Controllers\V9;

use App\Constants\BannersConstant;
use App\Constants\LieXiongConstant;
use App\Constants\UserVipConstant;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Helpers\RestResponseFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserOrderFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Factory\UserVipFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\BannersFactory;
use App\Models\Orm\UserCertificate;
use App\Models\Orm\UserRealname;
use App\Services\LieXiong\LieXiong;
use App\Strategies\BannerStrategy;
use App\Strategies\UserStrategy;
use App\Strategies\UserVipStrategy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    /**
     * 用户登录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function vipType(Request $request)
    {
        $user = $request->user();
        $phone = $user->mobile;
        $userId = $user->sd_user_id;
        //var_dump('abc');exit;
        $res = [
            //'token' => '',
            'vip_type' => 0,
            'url' => '',
        ];


        // 会员类型 vip_type  0不是会员，1平台会员，2烈熊会员
        $res['vip_type'] = UserVipFactory::getUserVipType($userId);

        if ($res['vip_type'] == 2) {
            $service = new LieXiong();
            $token = $service->userLogin($phone, $userId);

            if (empty($token)) {
                return RestResponseFactory::unauthorized('login fail');
            }
            //$res['token'] = $token;
            $res['url'] = env('LIEXIONG_GOTO_URL') . sprintf("?_action=gotoHome&_token=%s", $token);
            //$res['url'] = 'https://qy-h5-dev.billbear.cn/?_debug=1#/pages/index/index' . "&_token=" . $token;
        } else if ($res['vip_type'] == 1) {
            $res['url'] = env('APP_URL') . '/view/v2/users/vip/centers';
        }

        return RestResponseFactory::ok($res);
    }

    /**
     * 购买会员
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyVip(Request $request)
    {
        $cardId = $request->input('cardId', '');
        $terminalType = $request->input('terminalType', '');
        $terminalId = $request->input('terminalId', '');
        $schema = $request->input('schema', 'sdzj://splash');
        //$successUrl = $request->input('successUrl', 'http://uat.nest.sudaizhijia.com/#/transfer');

        $user = $request->user();
        $phone = $user->mobile;
        $userId = $user->sd_user_id;

        DB::beginTransaction();

        try {
            $service = new LieXiong();
            $res = $service->vipCard();
            $res = empty($res) ? [] : array_column($res, null, 'id');

            if (empty($res) || !isset($res[$cardId])) {
                logError('cardid unknown', ['cardId' => $cardId]);
                throw new \Exception('cardid unknown');
            }

            $orderId = 'SD-LX-' . strtoupper(str_random(20));
            $orderInfo = [
                'cardId' => $cardId,
                'orderId' => $orderId,
                'schema' => $schema,
                //'successUrl' => $successUrl,
            ];

            $service = new LieXiong();
            $result = $service->buyCard($phone, $userId, $orderInfo);

            if (empty($result['orderId'])) {
                logError('liexiong order create fail', ['orderInfo' => $orderInfo]);
                throw new \Exception('liexiong order create fail');
            }

            $cardInfo = $res[$cardId];

            $orderInfo = [
                'user_id' => $userId,
                'orderid' => $orderId,
                'payment_order_id' => $result['orderId'],
                'order_type' => LieXiongConstant::ORDER_TYPE[$cardInfo['cycleType']],  //订单类型
                'payment_type' => LieXiongConstant::PAYMENT_TYPE,//支付类型
                'terminaltype' => $terminalType,
                'terminalid' => $terminalId,
                'user_agent' => UserAgent::i()->getUserAgent(),
                'created_ip' => Utils::ipAddress(),
                'created_at' => date('Y-m-d H:i:s'),
                'request_text' => json_encode($cardInfo),
                'amount' => bcdiv($cardInfo['sellingPrice'], 100, 2),
                'subtype' =>  LieXiongConstant::VIP_TYPE[$cardInfo['cycleType']],
                'pay_type' => 5,
            ];
            $res = UserOrderFactory::createOrder($orderInfo);

            logDebug('order info', ['orderInfo' => $orderInfo]);

            if (!$res) {
                logError('order create fail', ['orderInfo' => $orderInfo]);
                throw new \Exception('order create fail');
            }

            $vipInfo = [
                'user_id' => $userId,
                'vip_type' => 1,
                'subtype_id' => LieXiongConstant::VIP_TYPE[$cardInfo['cycleType']],
                'vip_no' => UserVipStrategy::generateId(UserVipFactory::getVipLastId()),
            ];

            $res = UserVipFactory::createVipInfo($vipInfo);

            if (!$res) {
                logError('user_vip create fail', ['vipInfo' => $vipInfo]);
                throw new \Exception('user_vip create fail');
            }

            DB::commit();

            $data = [
                'url' => $result['url'],
                'orderId' => $orderId,
            ];

            return RestResponseFactory::ok($data);
        } catch (\Exception $e) {
            logError('order create fail', ['error' => $e->getMessage()]);
            DB::rollBack();
            return RestResponseFactory::error("order create fail", 1136);
        }

    }

    /**
     * 会员卡列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cardList(Request $request)
    {
        $service = new LieXiong();
        $res = $service->vipCard();

        return RestResponseFactory::ok($res);
    }

    /**
     * 用户连登福利
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginWelfare(Request $request) {

        //用户id
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        //用户登录信息
        $userLogin = UserFactory::fetchUserUnlockLoginTotalByUserId($userId);

        if (empty($userLogin)) {
            return RestResponseFactory::error(null,1000);
        }

        //连登解锁规则
        $bannerUnlockLoginNewUser = BannersFactory::fetchBannerUnlockLoginNewUserByTypeId(1);

        if (empty($bannerUnlockLoginNewUser)) {
            return RestResponseFactory::error(null,1002);
        }

        //用户最近连登天数
        $userLoginConsecutiveDays = $userLogin['near_login_count'];

        //新用户/连登1天老用户基础产品数
        $baseNum = $bannerUnlockLoginNewUser['unlock_pro_num'];

        //新用户/连登1天老用户每连登1天增加产品数
        $consecutiveNum = $bannerUnlockLoginNewUser['login_pro_num'];

        $rule  = '<p>用户第1天注册成功，可查看'.$baseNum.'款产品；</p>';
        $rule .= '<p>第2天再次登录，此时比昨天可多查看'.$consecutiveNum.'款产品，即此时可查看'.strval($baseNum+$consecutiveNum).'款产品；</p>';
        $rule .= '<p>第3天该用户未登录，第4天、第5天、第6天该用户连续登录3天，那么第6日该用户登录后可再多查看'.$consecutiveNum.'款产品，即可查看'.strval($baseNum+$consecutiveNum*2).'款产品。</p>';

        $re = ['userLoginConsecutiveDays'=>$userLoginConsecutiveDays,'baseNum'=>$baseNum,'consecutiveNum'=>$consecutiveNum,'rule'=>$rule];

        return RestResponseFactory::ok($re);
    }

    /**
     * 用户动态
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function feed()
    {
        $memberActivity = UserVipFactory::fetchMemberActivityInfo();

        return RestResponseFactory::ok($memberActivity);

    }

    /**
     * 用户基础信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request)
    {
        $user = $request->user();
        $userId = $user->sd_user_id;
        $mobile = $user->mobile;

        $res = [
            'user_id' => $userId,
            'realname' => '',
            'certificate_no' => '',
            'mobile' => '',
        ];

        $userinfo = UserCertificate::select(['realname', 'certificate_no', 'mobile'])
            ->where(['user_id' => $userId])
            ->where(['status' => 1])
            ->orderBy('id', 'desc')
            ->first();

        if (!empty($userinfo)) {
            $res['realname'] = $userinfo->realname;
            $res['certificate_no'] = $userinfo->certificate_no;
            $res['mobile'] = $userinfo->mobile;
            return RestResponseFactory::ok($res);
        }

        $userinfo = UserRealname::select(['realname', 'certificate_no'])
            ->where(['user_id' => $userId])
            ->orderBy('id', 'desc')
            ->first();

        if (!empty($userinfo)) {
            $res['realname'] = $userinfo->realname;
            $res['certificate_no'] = $userinfo->certificate_no;
            $res['mobile'] = $mobile;
        }

        return RestResponseFactory::ok($res);
    }
}