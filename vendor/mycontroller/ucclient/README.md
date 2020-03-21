## UCClient for Laravel5

本项目基于 [wehnhew/laravel4-ucenter](https://github.com/wehnhew/laravel4-ucenter) 做了一点儿微小的升级，感谢 wehnhew ！

### 安装

  ```shell
  composer require mycontroller/ucclient
  ```

## 配置

在 `/config/app.php` 文件中找到 `providers` 键，

  ```shell
  'providers' => [
    ...
    MyController\UCClient\UCenterService\UCenterServiceProvider::class,
    ...
  ];
  ```

在 `/config/app.php` 文件中找到 `aliases` 键，

  ```shell
  'aliases' => [
    ...
    'UCClient' => MyController\UCClient\Facades\UCClientFacade::class,
    ...
  ];
  ```

如果想自定义配置, 可以运行以下命令将配置文件复制到 `/config/uc-client.php` , 之后就可以方便的自定义了

  ```shell
  php artisan config:publish
  ```

## 使用

例如：获取用户名为wen的信息
  ```shell
  $result = UCClient::execute('uc_get_user',['wen']);
  dd($result);
  ```
  
## 关于SSO登录注销

您需要自己实现 UCenterSSOContract 接口, 并将 UCenterSSOContract的具体实现类 绑定至 UCenterSSOContract 接口。

例如可以实现:
 ```shell
 <?php
 
 namespace App;
 
 use MyController\UCClient\Contracts\UCenterSSOContract;
 
 class MyUCenterSSO implements UCenterSSOContract
 {
     public function synLogin($uid, $username = '')
     {
         /** 同步登录代码 **/
     }
 
     public function synLogout()
     {
         /** 同步注销代码 **/
     }
 }
 ```
 
然后在 App\Providers\AppServiceProvider 的 register方法 里增加:
  ```shell
  $this->app->bind(
      \MyController\UCClient\Contracts\UCenterSSOContract::class,
      \App\MyUCenterSSO::class
  );
  ```
  
## 避免开启了 barryvdh/laravel-debugbar 插件后影响 UCenterAPI 的输出结果

您需要自己实现 UCenterAPIExecuteFilterContract 接口, 并将 UCenterAPIExecuteFilterContract的具体实现类 绑定至 UCenterAPIExecuteFilterContract 接口。

例如可以实现:
 ```shell
 <?php
 
 namespace App;
 
 use MyController\UCClient\Contracts\UCenterAPIExecuteFilterContract;
 
 class MyUCenterAPIExecuteFilter implements UCenterAPIExecuteFilterContract
 {
     public function beforeRun()
     {
         //
     }
 
     public function afterRun()
     {
         //
         \Debugbar::disable(); //Runtime 关闭 debugbar
     }
 }
 ```
 
然后在 App\Providers\AppServiceProvider 的 register方法 里增加:
  ```shell
  $this->app->bind(
      \MyController\UCClient\Contracts\UCenterAPIExecuteFilterContract::class,
      \App\MyUCenterAPIExecuteFilter::class
  );
  ```
  
## License

MIT
