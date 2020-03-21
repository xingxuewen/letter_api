<?php

namespace MyController\UCClient\Contracts;

 /*
 |-------------------------------------------------------------
 | 您需要自己实现 UCenterSSOContract 接口, 并将 UCenterSSOContract的具体实现类 绑定至 UCenterSSOContract
 | 在 App\Providers\AppServiceProvider 的 register() 里增加:
 | $this->app->bind('MyController\UCClient\Contracts\UCenterSSOContract', 'UCenterSSOContract的具体实现类');
 |-------------------------------------------------------------
 */
interface UCenterSSOContract
{
    const API_RETURN_SUCCEED = 1;
    const API_RETURN_FAILED = -1;
    const API_RETURN_FORBIDDEN = -2;

    /**
     * 同步登录
     *
     * @param integer $uid
     * @param string $username
     * @return mixed
     */
    public function synLogin($uid, $username = '');

    /**
     * 同步注销
     *
     * @return mixed
     */
    public function synLogout();
}
