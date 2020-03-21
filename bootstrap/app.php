<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/**
  |--------------------------------------------------------------------------
  | Create The Application
  |--------------------------------------------------------------------------
  |
  | Here we will load the environment and create the application instance
  | that serves as the central piece of this framework. We'll use this
  | application as an "IoC" container and router for this framework.
  |
 */
$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->withFacades();
$app->withEloquent();

/**
  |--------------------------------------------------------------------------
  | Register Container Bindings
  |--------------------------------------------------------------------------
  |
  | Now we will register a few bindings in the service container. We will
  | register the exception handler and the console kernel. You may add
  | your own bindings here if you like or you can make another file.
  |
 */
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/**
  |--------------------------------------------------------------------------
  | Register Middleware
  |--------------------------------------------------------------------------
  |
  | Next, we will register the middleware with the application. These can
  | be global middleware that run before and after each request into a
  | route or middleware that'll be assigned to some specific routes.
  |
 */
$app->middleware([
    \Barryvdh\Cors\HandleCors::class,
    \App\Http\Middleware\BeforeMiddleware::class,
    \App\Http\Middleware\AfterMiddleware::class,
    \App\Http\Middleware\TerminateMiddleware::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'validate' => App\Http\Middleware\ValidateMiddleware::class,
    'analysis' => App\Http\Middleware\AnalysisMiddleware::class,
    'sign' => App\Http\Middleware\SignMiddleware::class,
    'api_cros' => App\Http\Middleware\CrossMiddleware::class,
    'cros' => \Barryvdh\Cors\HandleCors::class,
    //敏感词过滤
    'sensitive' => App\Http\Middleware\SensitiveMiddleware::class
]);

/**
  |--------------------------------------------------------------------------
  | Register Service Providers
  |--------------------------------------------------------------------------
  |
  | Here we will register all of the application's service providers which
  | are used to bind services into the container. Service providers are
  | totally optional, so you are not required to uncomment this line.
  |
 */
$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);

/**
  |--------------------------------------------------------------------------
  | Register Framework Service Providers
  |--------------------------------------------------------------------------
 */
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);

/**
  |--------------------------------------------------------------------------
  | Register Third Service Providers
  |--------------------------------------------------------------------------
 */
$app->register(Mnabialek\LaravelSqlLogger\Providers\ServiceProvider::class);
$app->register(SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class);
$app->register(Barryvdh\Cors\ServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);
$app->register(Jenssegers\Agent\AgentServiceProvider::class);

/**
  |--------------------------------------------------------------------------
  | Register Laravel Service Providers
  |--------------------------------------------------------------------------
 */
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Illuminate\Routing\RoutingServiceProvider::class);

/**
  |--------------------------------------------------------------------------
  | Register Laravel Face Aliases
  |--------------------------------------------------------------------------
 */
$app->withAliases([
    SimpleSoftwareIO\QrCode\Facades\QrCode::class => 'QrCode',
    Jenssegers\Agent\Facades\Agent::class => 'Agent'
]);

/**
  |--------------------------------------------------------------------------
  | Bind Laravel Manager
  |--------------------------------------------------------------------------
 */
$app->bind(\Illuminate\Cache\CacheManager::class, function ($app) {
    return new \Illuminate\Cache\CacheManager($app);
});
/**
  |--------------------------------------------------------------------------
  | Register Lumen Config
  |--------------------------------------------------------------------------
 */
$app->configure('cors');        // 跨域请求
$app->configure('captcha');     // 二维码
$app->configure('sms');         // 短信
$app->configure('sudai');       // 速贷配置
$app->configure('banner_circle');       // 速贷配置


/**
 *  判断当前是生产环境
 */
define("PRODUCTION_ENV", (env('APP_ENV') == 'production'));
/**
 * 辅助全局函数
 */
require __DIR__ . '/helpers.php';

date_default_timezone_set('Asia/Shanghai'); //时区配置

// 日志配置
$app->configureMonologUsing(function($monolog) {
    //$monolog = $monolog->withName('sudai');
    $logFormat = "[%datetime%] %level_name% extra=%extra%||msg=%message%||context=%context%\n";
    $dateFormat = "Y-m-d H:i:s.u";
    $formatObj = new \Monolog\Formatter\LineFormatter($logFormat, $dateFormat, false, true);

    if (PRODUCTION_ENV) {
        $logPath = '/logs/php_logs/api.log';
    } else {
        $logPath = storage_path() . '/logs/api.log';
    }

    $infoStream = new StreamHandler($logPath, Logger::INFO, true, 0777);
    $infoStream->setFormatter($formatObj);
    $monolog->pushHandler($infoStream);

    $errorStream = new StreamHandler($logPath . '.wf', Logger::ERROR, true, 0777);
    $errorStream->setFormatter($formatObj);
    $monolog->pushHandler($errorStream);

    $warningStream = new StreamHandler($logPath . '.wf', Logger::WARNING, true, 0777);
    $warningStream->setFormatter($formatObj);
    $monolog->pushHandler($warningStream);

    $monolog->pushProcessor(new \Monolog\Processor\ProcessIdProcessor());
    $monolog->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor());
    $monolog->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor());
    $monolog->pushProcessor(new \Monolog\Processor\MemoryPeakUsageProcessor());
    $monolog->pushProcessor(new \Monolog\Processor\WebProcessor(null, [
        'uri' => 'REQUEST_URI',
        'ip' => 'REMOTE_ADDR',
        'http_method' => 'REQUEST_METHOD',
        'server' => 'SERVER_NAME',
        'referrer' => 'HTTP_REFERER',
        'ua' => 'HTTP_USER_AGENT',
        'query'=> 'QUERY_STRING',
        'server_ip' => 'SERVER_ADDR'
    ]));
    $monolog->pushProcessor(function ($record) {
        if (!empty($record['context'])) {
            //$context = '';
            foreach ($record['context'] as $key => $val) {
                if (is_object($val) && !method_exists($val, "__toString")) {
                    $record['context'][$key] = json_encode($val);
                }
            }
            //$record['context'] = 'aa=bb||cc=dd';
        }
        $record['extra']['trace_id'] = getTraceId();
        $record['extra']['request_id'] = getRequestId();

        $info = debug_backtrace();
        //print_r($info[5]);exit;
        if (!empty($info[5])) {
            $record['extra']['file'] = $info[5]['file'] ?? '';
            $record['extra']['line'] = $info[5]['line'] ?? '';
        }
        if (!empty($info[6])) {
            $record['extra']['class'] = $info[6]['class'] ?? '';
            $record['extra']['func'] = $info[6]['function'] ?? '';
        }
        return $record;
    });
    $monolog->setTimezone(new \DateTimeZone('PRC'));

    return $monolog;
});

/**
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
 */
$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});


return $app;
