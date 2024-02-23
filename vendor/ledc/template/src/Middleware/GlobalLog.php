<?php

namespace Ledc\Template\Middleware;

use Exception;
use Illuminate\Database\Schema\Blueprint;
use support\Context;
use support\Db;
use support\Log;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 全局日志中间件.
 */
class GlobalLog implements MiddlewareInterface
{
    /**
     * 契约方法
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        $start = microtime(true);
        $trace_id = $start . 's' . uniqid(mt_rand(10000, 99999), true);
        // 获取请求信息
        $data = [
            'ip' => $this->getIp($request),
            'uri' => $request->uri(),
            'method' => $request->method(),
            'appid' => '', // TODO 业务数据，如果项目中可直接获取到appid，记录在此处
            'trace_id' => $request->header('trace_id', $trace_id),
            'referer' => $request->header('referer'),
            'user_agent' => $request->header('user-agent'),
            'query' => $request->all(),
            'cookie' => $request->cookie(),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // 记录全局trace_id
        Context::set('trace_id', $data['trace_id']);

        /** @var Response $response */
        $response = $handler($request);
        $err = $response->exception();
        if ($err instanceof Exception) {
            $trace = [$err->getMessage(), $err->getFile(), $err->getLine(), $err->getTraceAsString()];
            $data['exception'] = json_encode($trace, JSON_UNESCAPED_UNICODE);
            // 这个统一异常时的接口响应
        }

        $data['errcode'] = $response->getStatusCode();
        $rawBody = $response->rawBody();
        if (60000 < strlen($rawBody)) {
            $date = date('Ymd');
            $filepath = "/logs/$date/$trace_id.log";
            $filename = runtime_path() . $filepath;
            $parent_dir = dirname($filename);
            if (!is_dir($parent_dir)) {
                mkdir($parent_dir, 0777, true);
            }
            file_put_contents($filename, $rawBody);
            $rawBody = $filepath;
        }
        $data['response'] = $rawBody;
        $end = microtime(true);
        $exec_time = round(($end - $start) * 1000, 2);
        $data['exec_time'] = $exec_time;
        // 投递到异步队列
        $this->consume($data);

        return $response;
    }

    /**
     * @return false|string
     */
    private function getIp(Request $request): bool|string
    {
        $forward_ip = $request->header('X-Forwarded-For');
        $ip1 = $request->header('x-real-ip');
        $ip2 = $request->header('remote_addr');
        if (!$ip1 && !$ip2 && !$forward_ip) {
            return false;
        }
        $request_ips = [];
        if ($forward_ip) {
            $request_ips[] = $forward_ip;
        }
        if ($ip1) {
            $request_ips[] = $ip1;
        }
        if ($ip2) {
            $request_ips[] = $ip2;
        }

        return implode(',', $request_ips);
    }

    /**
     * 消费方法
     * @param array $data
     * @return void
     */
    public function consume(array $data): void
    {
        try {
            $tableName = 'global_log_' . date('Ymd');
            $this->initTable($tableName);

            $cookie = $data['cookie'] ?? [];
            $appid = $data['appid'] ?? '';
            $query = $data['query'] ?? [];

            DB::table($tableName)->insert([
                'ip' => $data['ip'] ?? '',
                'uri' => $data['uri'] ?? '',
                'method' => $data['method'] ?? '',
                'appid' => $appid,
                'trace_id' => $data['trace_id'] ?? '',
                'referer' => $data['referer'] ?? '',
                'user_agent' => $data['user_agent'] ?? '',
                'query' => $query ? json_encode($query, JSON_UNESCAPED_UNICODE) : '',
                'errcode' => $data['errcode'] ?? '',
                'response' => $data['response'] ?? '',
                'exception' => $data['exception'] ?? '',
                'exec_time' => $data['exec_time'] ?? '',
                'cookie' => $cookie ? json_encode($data['cookie'], JSON_UNESCAPED_UNICODE) : '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            Log::error('global_log_queue_error', [
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * 初始化表
     * - 判断global_log表是否存在，按天分表
     * @param string $tableName
     * @return void
     */
    private function initTable(string $tableName): void
    {
        if (!Db::schema()->hasTable($tableName)) {
            Db::schema()->create($tableName, function (Blueprint $table) {
                $table->increments('id')->autoIncrement()->unsigned();
                $table->string('ip', 200)->nullable(true)->default(null)->comment('访问ip');
                $table->string('uri', 255)->nullable(true)->default(null)->comment('访问uri');
                $table->string('method', 10)->nullable(true)->default(null)->comment('请求方法');
                $table->string('appid', 50)->nullable(true)->default(null)->comment('应用appid');
                $table->string('trace_id', 255)->nullable(true)->default(null)->comment('trace_id');
                $table->text('referer')->nullable(true)->default(null)->comment('来源页');
                $table->text('user_agent')->nullable(true)->default(null)->comment('user_agent');
                $table->text('query')->nullable(true)->default(null)->comment('请求参数');
                $table->string('errcode', 10)->nullable(true)->default(null)->comment('响应错误码');
                $table->text('response')->nullable(true)->default(null)->comment('响应结果');
                $table->text('exception')->nullable(true)->default(null)->comment('异常信息');
                $table->text('exec_time')->nullable(true)->default(null)->comment('执行毫秒');
                $table->text('cookie')->nullable(true)->default(null)->comment('请求cookie');
                $table->dateTime('created_at')->nullable(true)->default(null)->comment('创建时间');

                $table->index('ip', 'ip');
                $table->index('uri', 'uri');
                $table->index('appid', 'appid');
                $table->index('trace_id', 'trace_id');
                $table->index('created_at', 'created_at');

                $table->comment('日志');
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                $table->engine = 'InnoDB';
            });
        }
    }
}
