<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Helpers\RestResponseFactory;

/**
 * @author zhaoqiying
 */
class Handler extends ExceptionHandler
{

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
	    MethodNotAllowedHttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        //记录异常
        if ($e instanceof NotFoundHttpException)
        {
            logInfo('NotFoundHttpException', ['msg' => $e->getMessage()]);
            return RestResponseFactory::notfound('Not Found');
        }
        elseif ($e instanceof ModelNotFoundException)
        {
            logError('ModelNotFoundException', ['msg' => $e->getMessage()]);
            return RestResponseFactory::notfound($e->getMessage() ?: 'Model Not Found', 9, $e->getMessage());
        }
        elseif ($e instanceof BadRequestHttpException)
        {
            logError('BadRequestHttpException', ['msg' => $e->getMessage(), 'err' => $e->getTraceAsString()]);
            return RestResponseFactory::badrequest('出错啦,请重试(400)', 9, '出错啦,请重试(400)');
        }
        elseif ($e instanceof HttpException)
        {
            if ($e->getStatusCode() == 503)
            {
                logError('HttpException', ['msg' => $e->getMessage(), 'err' => $e->getTraceAsString()]);
                return RestResponseFactory::any(null, 503, '系统维护中, 请稍后访问', 9, '系统维护中, 请稍后访问');
            }
        }
        elseif ($e instanceof FatalErrorException)
        {
            logError('FatalErrorException', ['msg' => $e->getMessage(), 'err' => $e->getTraceAsString()]);
            return RestResponseFactory::error('出错啦,请重试, 或联系客服(500)', 9, '出错啦,请重试, 或联系客服(500)');
        }
        elseif ($e instanceof MethodNotAllowedHttpException)
        {
            logInfo('MethodNotAllowedHttpException', ['msg' => $e->getMessage(), 'err' => $e->getTraceAsString()]);
            return RestResponseFactory::any(null, 405, 'Http Method Not Allowed', 9, 'Http Method Not Allowed');
        }
        else
        {
            logError('Unknown Error', ['msg' => $e->getMessage(), 'err' => $e->getTraceAsString()]);
            return RestResponseFactory::badrequest($e->getMessage() ? $e->getMessage() : 'API Error');
        }
        return parent::render($request, $e);
    }

}
