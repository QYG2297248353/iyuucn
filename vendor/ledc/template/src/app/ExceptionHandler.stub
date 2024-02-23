<?php

namespace app;

use support\exception\BusinessException;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;

/**
 * 异常信息以Json返回
 */
class ExceptionHandler extends \Webman\Exception\ExceptionHandler
{
    /**
     * @var string[]
     */
    public $dontReport = [
        BusinessException::class,
    ];

    /**
     * 异常白名单
     * - 在白名单内，返回详细的异常描述
     * @var array
     */
    const whiteListException = [
        BusinessException::class,
    ];

    /**
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * 渲染返回
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response
    {
        if (($exception instanceof BusinessException) && ($response = $exception->render($request))) {
            return $response;
        }

        $header = [
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache', //禁止缓存
            'Pragma' => 'no-cache', //禁止缓存
        ];

        $rs = [
            'code' => $exception->getCode() ?: 500,
            'msg' => match (true) {
                $this->debug, $this->canWhiteList($exception) => $exception->getMessage(),
                default => 'server internal error',
            },
        ];
        if ($this->debug) {
            $rs['traces'] = (string)$exception;
        }

        return new Response(200, $header, json_encode($rs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param Throwable $exception
     * @return bool
     */
    private function canWhiteList(Throwable $exception): bool
    {
        foreach (static::whiteListException as $type) {
            if ($exception instanceof $type) {
                return true;
            }
        }
        return false;
    }
}