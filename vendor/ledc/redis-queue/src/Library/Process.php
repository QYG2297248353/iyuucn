<?php

namespace Ledc\RedisQueue\Library;

use Closure;
use Ledc\Redis\Redis;
use Ledc\Redis\RedisQueueClient;
use Ledc\RedisQueue\ConsumerAbstract;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use support\Container;
use support\Log;
use Throwable;
use Workerman\Timer;
use Workerman\Worker;

/**
 * Redis队列消费者进程
 */
class Process
{
    /**
     * 消费者目录
     * @var string
     */
    protected string $_consumerDir;
    /**
     * 处理失败队列的回调函数
     * @var Closure|null
     */
    protected Closure|null $failed_callback = null;
    /**
     * 当前进程的worker
     * @var Worker|null
     */
    protected static ?Worker $worker = null;

    /**
     * 构造函数 constructor.
     * @param string $consumer_dir 消费者目录
     * @param Closure|null $failed_callback 处理失败队列的回调函数
     */
    public function __construct(string $consumer_dir = '', Closure $failed_callback = null)
    {
        $this->_consumerDir = $consumer_dir;
        $this->failed_callback = $failed_callback;
    }

    /**
     * onWorkerStart
     * @param Worker $worker
     * @return void
     */
    public function onWorkerStart(Worker $worker): void
    {
        static::$worker = $worker;
        if (!is_dir($this->_consumerDir)) {
            echo "Consumer directory {$this->_consumerDir} not exists\r\n";
            return;
        }

        //多进程jobs
        $consumer = new JobsConsumer();
        $connection_name = $consumer->subscribe();
        //处理失败队列的回调函数
        $this->failedCallback($connection_name);

        //多进程队列
        $dir_iterator = new RecursiveDirectoryIterator($this->_consumerDir);
        $iterator = new RecursiveIteratorIterator($dir_iterator);
        foreach ($iterator as $file) {
            if (is_dir($file)) {
                continue;
            }
            $fileInfo = new SplFileInfo($file);
            $ext = $fileInfo->getExtension();
            if ('php' === $ext) {
                $class = str_replace('/', "\\", substr(substr($file, strlen(base_path())), 0, -4));
                if (is_a($class, ConsumerAbstract::class, true)) {
                    /** @var ConsumerAbstract $consumer */
                    $consumer = Container::get($class);
                    $connection_name = $consumer->subscribe();
                    //处理失败队列的回调函数
                    $this->failedCallback($connection_name);
                }
            }
        }
    }

    /**
     * 处理失败队列
     * @param string $connection_name 连接名，对应 config/redis-queue.php 里的连接
     * @return void
     */
    protected function failedCallback(string $connection_name): void
    {
        static $unique = [];
        if (!isset($unique[$connection_name])) {
            //处理失败队列
            $unique[$connection_name] = Timer::add(5, function () use ($connection_name) {
                try {
                    $redis = Redis::connection($connection_name);
                    if ($redis->lLen(RedisQueueClient::QUEUE_FAILED)) {
                        while ($package = $redis->rPop(RedisQueueClient::QUEUE_FAILED)) {
                            if ($this->failed_callback instanceof Closure) {
                                call_user_func($this->failed_callback, $package, $connection_name);
                            }
                        }
                    }
                } catch (Throwable $throwable) {
                    Log::warning('=====>>> 处理失败队列，异常：' . $throwable->getMessage());
                }
            });
        }
    }

    /**
     * 获取worker.
     * @return Worker|null
     */
    public static function worker(): ?Worker
    {
        return static::$worker;
    }
}
