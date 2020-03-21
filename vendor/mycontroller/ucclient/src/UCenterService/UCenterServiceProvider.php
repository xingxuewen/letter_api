<?php

namespace MyController\UCClient\UCenterService;

use Illuminate\Support\ServiceProvider;

class UCenterServiceProvider extends ServiceProvider
{
    /**
     * 指定提供者加载是否延缓。
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * 运行注册后的启动服务。
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../../config/uc-client.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('uc-client.php');
        } else {
            $publishPath = base_path('config/uc-client.php');
        }
        $this->publishes([$configPath => $publishPath], 'config');

        // HTTP routing
        if (strpos($this->app->version(), 'Lumen') !== false) {
            $this->app->get($this->app['config']->get('uc-client.url', '/api/uc'), function () {
                return UCenterAPI::execute(app('MyController\UCClient\Contracts\UCenterAPIExecuteFilterContract'));
            });
            $this->app->post($this->app['config']->get('uc-client.url', '/api/uc'), function () {
                return UCenterAPI::execute(app('MyController\UCClient\Contracts\UCenterAPIExecuteFilterContract'));
            });
        } else {
            if (strpos($this->app->version(), '5.2.') !== false || strpos($this->app->version(), '5.3.') !== false) {
                $this->app['router']->group(['middleware' => 'web',], function () {
                    $this->app['router']->any($this->app['config']->get('uc-client.url', '/api/uc'), function () {
                        return UCenterAPI::execute(app('MyController\UCClient\Contracts\UCenterAPIExecuteFilterContract'));
                    });
                });
            } else {
                $this->app['router']->any($this->app['config']->get('uc-client.url', '/api/uc'), function () {
                    return UCenterAPI::execute(app('MyController\UCClient\Contracts\UCenterAPIExecuteFilterContract'));
                });
            }
        }
    }

    /**
     * 注册服务提供者。
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../../config/uc-client.php';
        $this->mergeConfigFrom($configPath, 'uc-client');

        $this->app->singleton('ucenter.client', function ($app) {
            return new UCenterClient();
        });
    }

    /**
     * 获取提供者所提供的服务。
     * PS: defer 属性设置为 true 时会使用本方法
     *
     * @return array
     */
    public function provides()
    {
        return array('ucenter.client');
    }
}
