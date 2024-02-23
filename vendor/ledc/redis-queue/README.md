# redis-queue
Message queue system written in PHP based on [workerman](https://github.com/walkor/workerman) and backed by Redis.

# 安装 Install
```
composer require ledc/redis-queue
```


# 使用 Usage

## 消费者用法
可以使用命令，创建队列的消费者

```shell
php webman redis-queue:consumer First
```

向队列投递任务：
```php
First::send(\request()->get());
```

向队列投递延时任务：
```php
First::send(\request()->get(), 5);
```


## 任务用法
可以使用命令，创建任务类

```shell
php webman redis-queue:jobs FirstJobs
```

投递任务：
```php
FirstJobs::dispatch([\request()->get()]);
```

投递延时任务：
```php
FirstJobs::dispatch([\request()->get()], 5);
```

投递任务，执行任务类的其他方法：
```php
$callable = [FirstJobs::class, 'example'];
FirstJobs::emit($callable, [$request->all()]);
```
提示：任务类的emit方法，可以调用任意类的公共方法，支持延时调用、支持向构造函数传参。
函数签名：
```php
/**
 * 调用任意类的公共方法
 * @param array $callable 可调用数组
 * @param array|bool|int|string|null $args 数据参数
 * @param int $delay 延时秒
 * @param array $constructor 构造函数参数
 * @return bool
 */
final public static function emit(array $callable = [], mixed $args = null, int $delay = 0, array $constructor = []): bool;
```

### 队列与任务的区别

队列是基础，任务是基于队列消费者做的封装。
用更灵活的函数调用，完成更细粒度的任务。


## 原生用法

test.php
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Lib\Timer;
use Ledc\Redis\RedisQueueClient;

$worker = new Worker();
$worker->onWorkerStart = function () {
    $client = new RedisQueueClient('redis://127.0.0.1:6379');
    $client->subscribe('user-1', function($data){
        echo "user-1\n";
        var_export($data);
    });
    $client->subscribe('user-2', function($data){
        echo "user-2\n";
        var_export($data);
    });
    Timer::add(1, function()use($client){
        $client->send('user-1', ['some', 'data']);
    });
};

Worker::runAll();
```

Run with command `php test.php start` or `php test.php start -d`.

# API

  * <a href="#construct"><code>Client::<b>__construct()</b></code></a>
  * <a href="#send"><code>Client::<b>send()</b></code></a>
  * <a href="#subscribe"><code>Client::<b>subscribe()</b></code></a>
  * <a href="#unsubscribe"><code>Client::<b>unsubscribe()</b></code></a>

-------------------------------------------------------

<a name="construct"></a>
### __construct (string $address, [array $options])

Create an instance by $address and $options.

  * `$address`  for example `redis://ip:6379`. 

  * `$options` is the client connection options. Defaults:
    * `auth`: default ''
    * `db`: default 0
    * `retry_seconds`: Retry interval after consumption failure
    * `max_attempts`: Maximum number of retries after consumption failure
   
-------------------------------------------------------

<a name="send"></a>
### send(String $queue, Mixed $data, [int $dely=0])

Send a message to a queue

* `$queue` is the queue to publish to, `String`
* `$data` is the message to publish, `Mixed`
* `$dely` is delay seconds for delayed consumption, `Int`
  
-------------------------------------------------------

<a name="subscribe"></a>
### subscribe(mixed $queue, callable $callback)

Subscribe to a queue or queues

* `$queue` is a `String` queue or an `Array` which has as keys the queue name to subscribe.
* `$callback` - `function (Mixed $data)`, `$data` is the data sent by `send($queue, $data)`.

-------------------------------------------------------

<a name="unsubscribe"></a>
### unsubscribe(mixed $queue)

Unsubscribe from a queue or queues

* `$queue` is a `String` queue or an array of queue to unsubscribe from

-------------------------------------------------------
