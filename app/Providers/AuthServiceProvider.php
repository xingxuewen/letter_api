<?php

namespace App\Providers;

use App\Models\Orm\UserAuth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Dusterio\LumenPassport\LumenPassport;
use Carbon\Carbon;
use App\Redis\RedisClientFactory;
use App\Models\Factory\AuthFactory;
use App\Helpers\Utils;

/**
 * @author zhaoqiying
 */
class AuthServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        /**
         *  Passport Config
         */
//        LumenPassport::allowMultipleTokens();
//        Passport::tokensExpireIn(Carbon::now()->addDays(15));
//        Passport::refreshTokensExpireIn(Carbon::now()->addDays(45));

        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {

            $token = $request->input('token') ?: $request->header('X-Token');

            if ($token) {

                $userInfo = UserAuth::where('accessToken', $token)->first();

                if (!empty($userInfo)) {

                    $userInfoArr = $userInfo->toArray();

                    //查询redis今天是否记录了用户登录信息，如果没记录则记录
                    $redis = RedisClientFactory::get();

                    $key = 'isCreateUserAuthLogin_'.$userInfoArr['sd_user_id'];

                    if (!$redis->exists($key)) {

                        $isCreateUserAuthLogin = $redis->set($key,'1');

                        $redis->expireAt($key, strtotime(date('Y-m-d 23:59:59')));

                        //添加日志，记录redis设置是否成功
                        logInfo('isCreateUserAuthLogin',['data'=>$isCreateUserAuthLogin,'user'=>$userInfoArr['sd_user_id']]);

                        //登录信息
                        $origin     = $request->header('origin') ?: '';
                        $referer    = $request->header('referer') ?: '';
                        $user_agent = $request->header('user_agent') ?: '';

                        $params = [];
                        $params['user_id']    = $userInfoArr['sd_user_id'];
                        $params['mobile']     = $userInfoArr['mobile'];
                        $params['password']   = $userInfoArr['password'];
                        $params['ip']         = Utils::ipAddress();
                        $params['origin']     = $origin;
                        $params['referer']    = $referer;
                        $params['user_agent'] = $user_agent;

                        AuthFactory::createUserAuthLogin($params);
                    }
                }

                return $userInfo;
            }
        });
    }
}