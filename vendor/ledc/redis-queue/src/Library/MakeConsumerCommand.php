<?php

namespace Ledc\RedisQueue\Library;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webman\Console\Util;

/**
 * make消费者
 */
class MakeConsumerCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'redis-queue:consumer';
    /**
     * @var string
     */
    protected static $defaultDescription = 'Make redis-queue consumer';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Consumer name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $output->writeln("Make consumer $name");

        $path = '';
        $namespace = 'app\\queue\\redis';
        if ($pos = strrpos($name, DIRECTORY_SEPARATOR)) {
            $path = substr($name, 0, $pos + 1);
            $name = substr($name, $pos + 1);
            $namespace .= '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', trim($path, DIRECTORY_SEPARATOR));
        }
        $class = Util::nameToClass($name);
        $queue = Util::classToName($name);

        $file = app_path() . "/queue/redis/{$path}$class.php";
        $this->createConsumer($namespace, $class, $queue, $file);

        return self::SUCCESS;
    }

    /**
     * @param $namespace
     * @param $class
     * @param $queue
     * @param $file
     * @return void
     */
    protected function createConsumer($namespace, $class, $queue, $file): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $controller_content = <<<EOF
<?php

namespace $namespace;

use Ledc\\Redis\\Payload;
use Ledc\\RedisQueue\\ConsumerAbstract;

/**
 * 消费者 
 */
class $class extends ConsumerAbstract
{
    /**
     * 要消费的队列名
     */
    public static function queue(): string
    {
        return '$queue';
    }

    /**
     * 连接名，对应 config/redis-queue.php 里的连接
     * @return string
     */
    public static function connection(): string
    {
        return 'default';
    }

    /**
     * 消费方法
     * - 消费过程中没有抛出异常和Error视为消费成功；否则消费失败,进入重试队列
     * - 也可以通过 \$payload 自定义重试间隔和重试次数
     * @param mixed \$data 数据
     * @param Payload \$payload 队列任务有效载荷
     * @return void
     */
    public function consume(mixed \$data, Payload \$payload): void
    {
        // 无需反序列化
        var_export(\$data);
    }
}

EOF;
        file_put_contents($file, $controller_content);
    }

}