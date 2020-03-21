<?php

namespace MyController\UCClient\Contracts;

 /*
 |-------------------------------------------------------------
 | 您需要自己实现 UCenterAPIExecuteFilterContract 接口, 并将 UCenterAPIExecuteFilterContract的具体实现类 绑定至 UCenterAPIExecuteFilterContract
 | 在 App\Providers\AppServiceProvider 的 register() 里增加:
 | $this->app->bind('MyController\UCClient\Contracts\UCenterAPIExecuteFilterContract', 'UCenterAPIExecuteFilterContract的具体实现类');
 |-------------------------------------------------------------
 */
interface UCenterAPIExecuteFilterContract
{
    public function beforeRun();

    public function afterRun();
}
