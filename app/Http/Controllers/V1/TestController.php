<?php

namespace App\Http\Controllers\V1;

use App\Constants\CreditConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Models\Factory\AccountFactory;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\CreditStatusFactory;
use App\Models\Factory\TestFactory;
use App\Models\Orm\CommentReply;
use App\Models\Orm\DataPlatformCommentCount;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\ProductTag;
use App\Models\Orm\TagSeo;
use App\Models\Orm\UserAccount;
use App\Models\Orm\UserAuth;
use App\Models\Orm\UserCredit;
use App\Models\Orm\UserInfo;
use App\Models\Orm\UserInvite;
use App\Services\Core\Payment\HuiJu\HuiJuService;
use App\Services\Core\Payment\PaymentService;
use App\Services\Core\Platform\Fangsiling\FangsilingService;
use App\Services\Core\Platform\Kami\Kami\KamiService;
use App\Services\Core\Platform\Quhuafenqi\QuhuafenqiService;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Services\Core\Validator\FaceId\FaceIdService;
use App\Services\Core\Validator\FaceId\Megvii\MegviiService;
use App\Services\Core\Validator\TianChuang\TianChuangService;
use App\Strategies\AccountLogStrategy;
use App\Strategies\UserStrategy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use  App\Services\Core\Platform\Rong360\Yuanzidai\YuanzidaiService;
use App\Services\Core\Platform\Shuixiang\Shuixiangfenqi\ShuixiangfenqiService;
use App\Services\Core\Platform\Jielebao\JielebaoService;

class TestController extends Controller
{

    public function test(Request $request)
    {
        //表单数组
        $formArray = $request->all();
        ksort($formArray);
        $sha1Text = '';
        foreach ($formArray as $key => $val) {
            $sha1Text = $sha1Text . $key . $val;
        }
        $token = ($request->input('token') ?: $request->header('X-Token')) ?: '';

        $startString = '';
        $endString = '';
        if (!empty($sha1Text)) {
            $startString = mb_substr($sha1Text, 0, 3);
            $endString = mb_substr($sha1Text, -3);
        }
        $url = $request->url();

        $salt = sha1($url);
        $sha1Text = $startString . $token . $endString . $salt;
        $sha1Sign = sha1($sha1Text);
        dd($sha1Sign);
        /*if ($sign !== $sha1Sign)
        {
            $message = '验签未通过,服务器验签:' . $sha1Sign . ';加密原串:' . $sha1Text;
            return RestResponseFactory::ok(null, $message, 409, $message);
        }*/
    }

    public function testGeetes()
    {
        $invites = UserInvite::select()->where(['user_id' => 999])->get()->toArray();
        dd($invites);
        return RestResponseFactory::ok(RestUtils::getStdObj(), 'success');
    }

    public function getCache()
    {
        $cache = CacheFactory::getValueFromCache('jpush_registration_id_953');
        dd($cache);
    }


    //将 sd_platform_comment表中的use_count add_count 同步到 sd_data_platform_comment表中
    public function comment()
    {
        $commentDatas = PlatformComment::select(['platform_comment_id', 'use_count', 'add_count'])
            ->where(['is_delete' => 0])
            ->orderBy('create_date', 'asc')
            ->orderBy('platform_comment_id', 'asc')
            ->chunk(100, function ($commentDatas) {
                DB::beginTransaction();
                try {
                    foreach ($commentDatas as $key => $value) {
                        $dataComment = DataPlatformCommentCount::updateOrCreate(['comment_id' => $value['platform_comment_id']],
                            [
                                'comment_id' => $value['platform_comment_id'],
                                'use_count' => $value['use_count'],
                                'add_count' => $value['add_count'],
                                'updated_ip' => Utils::ipAddress(),
                                'updated_at' => date('Y-m-d H:i:s', time()),
                            ]);
                        $dataComment->save();
                    }
                    //以上执行都成功，则对数据库进行实际执行
                    DB::commit();

                } catch (\Exception $e) {
                    //如果抛出错误则进入catch，先callback，然后捕获错误，返回错误
                    DB::rollBack();
                    logError('评论转移数据失败', $e->getMessage());
                }

            });
    }

    public function product()
    {
        $productIdArr = [171, 169, 170];
        $condition = implode(",", $productIdArr);
        $productLists = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $productIdArr)
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.loan_max', 'p.success_count'])
            ->orderByRaw(DB::raw("FIELD(`p.platform_product_id`," . $condition . ")"))
            ->get()->toArray();

        print_r($productLists);
        die();
    }

    /**
     * 日息 月息转化
     */
    public function formatRate()
    {
        $products = PlatformProduct::select(['platform_product_id', 'interest_alg', 'min_rate'])
            ->get()->toArray();

        DB::beginTransaction();

        foreach ($products as $key => $val) {
            //interest_alg 1月息 2日息
            if ($val['interest_alg'] == 1) {
                PlatformProduct::where(['platform_product_id' => $val['platform_product_id']])->update(['day_rate' => bcdiv($val['min_rate'], 30, 2)]);
                PlatformProduct::where(['platform_product_id' => $val['platform_product_id']])->update(['month_rate' => $val['min_rate']]);
            } elseif ($val['interest_alg'] == 2) {
                PlatformProduct::where(['platform_product_id' => $val['platform_product_id']])->update(['day_rate' => $val['min_rate']]);
                PlatformProduct::where(['platform_product_id' => $val['platform_product_id']])->update(['month_rate' => bcmul($val['min_rate'], 30, 2)]);
            }
        }

        DB::commit();

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @return bool
     * 修改标签类型
     */
    public function updateTags()
    {
        DB::beginTransaction();

        $tagsIds = TestFactory::fetchTagsIds();
        //dd($tagsIds);
        foreach ($tagsIds as $key => $val) {
            $ids = explode(',', $val['tag_id']);
            foreach ($ids as $k => $v) {
                $status = TagSeo::select()->where(['id' => $v, 'status' => 1])->first();
                if (empty($status)) {
                    ProductTag::where(['tag_id' => $v, 'status' => 1])->update(['status' => 9]);
                }
            }

        }
        DB::commit();

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    public function pregMatch()
    {
        $userAgent = '{"useragent":"Mozilla/5.0 (Linux; U; Android 5.1.1; zh-cn; MI NOTE Pro Build/LMY47V) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/53.0.2785.146 Mobile Safari/537.36 XiaoMi/MiuiBrowser/9.0.3"}';
        preg_match_all("/(?:\{)(.*)(?:\})/i", $userAgent, $result);
        dd($result[1][0]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 积分修订
     */
    public function updateCredit()
    {
        //查询expend为负值的数据
        $expend = CreditFactory::fetchExpends();
        //没有查出负值数据
        if (empty($expend)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //充值
        foreach ($expend as $key => $val) {
            try {
                DB::beginTransaction();
                $data['user_id'] = $val['user_id'];
                $data['expend'] = abs($val['expend']);
                $data['score'] = abs($val['expend']);
                $data['type'] = CreditConstant::EDIT_CREDIT_TYPE;
                $data['remark'] = CreditConstant::EDIT_CREDIT_REMARK;
                //插入流水记录
                $log = CreditFactory::createReduceCreditLog($data);
                //修改用户总积分
                $credit = CreditFactory::reduceUserCredit($data);
                if ($log && $credit) {
                    DB::commit();
                }
            } catch (Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
            }
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 修改账户
     */
    public function updateAccount()
    {
        //查询账户金额income小于0的数据
        $income = AccountFactory::fetchAccounts();
        //没有查出负值数据
        if (empty($income)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //dd($income);

        //充值
        foreach ($income as $key => $val) {
            try {
                DB::beginTransaction();
                $userId = $val['user_id'];
                //以前的数据
                $data['userAccount'] = AccountFactory::fetchUserAccountsArray($userId);
                $income_money = isset($val['income']) ? abs($val['income']) : 0;
                $data['expend_money'] = 0;
                if ($val['income'] < 0) {
                    $income_money = $val['expend'] + $income_money;
                } elseif ($val['income'] >= 0 && $val['total'] < 0) {
                    $income_money = abs($val['total']);
                }
                //类型
                $data['type'] = CreditConstant::EDIT_ACCOUNT_TYPE;
                $data['remark'] = CreditConstant::EDIT_ACCOUNT_REMARK;
                $data['userId'] = $userId;
                $data['income_money'] = $income_money;
                //用户账户流水表 数据处理
                $accountLog = AccountLogStrategy::getAccountLogs($data);
                //插入流水记录
                $log = AccountFactory::createAccountLog($accountLog);
                //修改总账户金额
                $account = AccountFactory::AddAccount($data);
                if ($log && $account) {
                    DB::commit();
                }
            } catch (Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
            }
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    public function replaceUsernameSd()
    {
        $data['username'] = 'sd_ewqewqewqe';
        $data['mobile'] = '13522960563';
        $res = UserStrategy::replaceUsernameSd($data);
        dd($res);
    }

    /**
     * 将已经创建头像的用户同步到状态表
     */
    public function createUserPhotoCreditStatus()
    {
        UserInfo::select(['user_id'])->where('user_photo', '!=', '')
            ->chunk(100, function ($users) {
                try {
                    DB::beginTransaction();

                    foreach ($users as $user) {
                        $data['typeNid'] = CreditConstant::ADD_INTEGRAL_USER_PHOTO_TYPE;
                        $data['remark'] = CreditConstant::ADD_INTEGRAL_USER_PHOTO_REMARK;
                        $data['typeId'] = CreditFactory::fetchIdByTypeNid($data['typeNid']);
                        $data['score'] = CreditFactory::fetchScoreByTypeNid($data['typeNid']);
                        $data['userId'] = $user['user_id'];

                        $res = CreditStatusFactory::updateCreditStatusById($data);

                        if ($res) {
                            DB::commit();
                        } else {
                            DB::rollBack();
                        }
                    }
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error('更新失败', $e->getMessage());
                }
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
            });
        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    public function alive()
    {
        $res = '{
   "time_used":320,
   "request_id":"1457432550,b70ab3a8-ee37-4f90-a2bd-007e23a970e2",
   "faces":[
      {
         "quality":38.22176746384912,
         "quality_threshold":30.1,
         "rect":{
            "left":0.18,
            "top":0.18,
            "width":0.5966667,
            "height":0.5966667
         },
         "orientation":90,
         "token":"Rihc25px-qjfYWBq5MRdy2HaE7FWKSaEj-J2qLEyMLY="
      }
   ]
}';
        $data = json_decode($res, true);
        print_r($data);
        die();
    }

    public function getChunkSplit()
    {
        $str = 'MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBALX1mg9SloM93MQCKM516ZvRAIQzwJ+dvlYPbYQNgQmYkX04swwAX56EbxGnkg2rb7GZ3/pqq1zvpNKB7hjMV0nnezGoylSOHmSkKrrdLvtpRf4NC96qDCrru2KiFW3BhfKy04+GYomSOF7Zgfshlxd4622B9KLVtT2uYW13jOpzAgMBAAECgYAPqLtBZlIdqU0+cREh83PPPQVWWz3QfrrKnTlHjAH22XJr3F1MQxv3gF4unsUq9/38wslLu7JTpSwCEbxz1eINDOaXJ/dqQZev5aQB2AUKQi4xLP55XrxcfQKZzgwsYtL6LeZUxjVvmcodttfAF/rnINyVJ4u5j+PcAsXMxvl3gQJBAOh6RXjxd37UgtWoVJDox0bgskYOXW+9fcHI+edkraubedV6dEtZirMbFKdMrrODTHk89z+FcWb6iiB0Ol6fMJkCQQDIXslkkIBrEeTVO0BZwqaNfuzbtDqLv9Ldu6G1stGPlxclOQfHdmTxjj4rbIqJLOOFJDP78vSgmAciXFW9gH7rAkEAz9zz3S3aMHcG/M7jviXEeGVUQTt64/xEQ07V08W7WyNLDkvNS8omL/rYvrXbxvpxGD4gvJUuTmtZsab6wbwIkQJANCcHwFckNbool5+edj6F31pkCCN3AZziI7iMtKBgj0FCUvvvHGmiiIT/hYnw3ReD+MmdhjyMk6g+YyEpZ/OkFQJARIqeaYDYKcixRAlu6MKcOz9xuI85GC6tQBQSXSKIBiQ2AbKw4EQn8B9V7emFS59a0GSAkdXxkpBWRRgbltplsA==';

        echo chunk_split($str, 64, "\n");

    }

    public function fetchVerifyMobileInfo3()
    {
        $data = [
            'mobile' => '13522960570',
            'idcard' => '540123199111246895',
            'name' => '蒋恒宏',
        ];

        $res = TianChuangService::authVerifyMobileInfo3C($data);
        dd($res);
    }

    public function fetchSubset()
    {

        $datas = [
            'userId' => 1288,
            'card_front' => QiniuService::getImgs('test/20180831/identity/idcard/sd_idcard_front_20180831135743-1288.jpg'),
            'card_photo' => QiniuService::getImgs('test/20180831/identity/idcard/sd_idcard_photo_20180831135743-1288.jpg'),
        ];
        $res = MegviiService::getAppBizToken($datas);
        dd($res);

        //指定第二个参数
        parse_str('dasdasdasdas=3&dasdsa=4', $parr);//$urlarr['query']的值为：uid=5&pages=2&category=3'
        print_r($parr);

        die();

        $minLength = 1;
        $in = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
        $count = count($in);
        $members = pow(2, $count);
        $return = array();
        for ($i = 0; $i < $members; $i++) {
            $b = sprintf("%0" . $count . "b", $i);
            $out = array();
            for ($j = 0; $j < $count; $j++) {
                if ($b{$j} == '1') $out[] = $in[$j];
            }
            if (count($out) >= $minLength) {
                $return[] = $out;
            }
        }

        return $return;
    }

    public function fetchshuixiang()
    {
//      $data['type'] = 2;
        $data['page'] = 1;
        $data['user'] = [
            'mobile' => '18989788899',
            'idcard' => '',
            'real_name' => '',
        ];

        $res = YuanzidaiService::fetchLoginService($data);
        dd($res);
    }


    public function fetchKami()
    {
        $data['type'] = 2;
        $data['page'] = 1;
        $data['user'] = [
            'mobile' => '13522960570',
        ];
        $res = KamiService::fetchLoginService($data);
        $res = ShuixiangfenqiService::fetchLoginService($data);
    }

    public function fetchQuhuafenqi()
    {
        $datas['user']['mobile'] = '18510536684';
        $datas['page'] = '';
        $res = QuhuafenqiService::fetchQuhuafenqiUrl($datas);
//        dd($res);
    }


    public function fetchYuanzidai()
    {
        $data['type'] = 2;
        $data['page'] = 1;
        $data['user'] = [
            'mobile' => '13522960570',
            'idcard' => '',
            'real_name' => '',
        ];
        $res = YuanzidaiService::fetchLoginService($data);
        dd($res);
    }

    public function fetchjielebao()
    {
//      $data['type'] = 2;
        $data['page'] = 1;
        $data['user'] = [
            'mobile' => '18989788898',
            'idcard' => '540123199111246894',
            'real_name' => '三十三岁',
        ];

        $res = JielebaoService::fetchLoginService($data);
        dd($res);
    }


    public function paymentByHuiJu()
    {
        $params['type'] = '';
        $data = [
            'orderNo' => PaymentService::i()->generateOrderId('SD', 'HUIJU'),
            'money' => '0.01',
            'productName' => '会员支付',
            'productDesc' => '会员支付',
            'url_params' => empty($params['type']) ? UserVipConstant::ORDER_TYPE : $params['type'],
        ];

        $res = HuiJuService::i()->orderPay($data);
    }

    public function fetchfangsiling()
    {
//      $data['type'] = 2;
        $data['page'] = 1;
        $data['user'] = [
            'mobile' => '18989788898',
            'idcard' => '540123199111246894',
            'real_name' => '三十三岁',
        ];

        $res = FangsilingService::fetchLoginService($data);

        dd($res);
    }
}

