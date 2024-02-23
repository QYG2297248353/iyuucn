<?php

namespace Ledc\RedisQueue\Library;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webman\Console\Util;

/**
 * make任务
 */
class MakeJobsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'redis-queue:jobs';
    /**
     * @var string
     */
    protected static $defaultDescription = 'Make redis-queue jobs';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Jobs name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $output->writeln("Make jobs $name");

        $path = '';
        $namespace = 'app\\jobs';
        if ($pos = strrpos($name, DIRECTORY_SEPARATOR)) {
            $path = substr($name, 0, $pos + 1);
            $name = substr($name, $pos + 1);
            $namespace .= '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', trim($path, DIRECTORY_SEPARATOR));
        }
        $class = Util::nameToClass($name);

        $file = app_path() . "/jobs/{$path}$class.php";
        $this->createConsumer($namespace, $class, $file);

        return self::SUCCESS;
    }

    /**
     * @param string $namespace
     * @param string $class
     * @param string $file
     * @return void
     */
    protected function createConsumer(string $namespace, string $class, string $file): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $content = <<<EOF
<?php

namespace $namespace;

use Ledc\\RedisQueue\\JobsAbstract;

/**
 * 任务 
 */
class $class extends JobsAbstract
{
    /**
     * 任务默认执行的方法
     * @param array \$data
     * @return void
     */
    public function execute(array \$data = []): void
    {
        // 无需反序列化
        var_export(\$data);
    }

    /**
     * 自定义的示例方法
     * @param array \$data
     * @return void
     */
    public function example(array \$data = []): void
    {
        // 无需反序列化
        var_export(\$data);
    }
}

EOF;
        file_put_contents($file, $content);
    }

}