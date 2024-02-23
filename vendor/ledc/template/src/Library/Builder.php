<?php

namespace Ledc\Template\Library;

use Doctrine\Inflector\InflectorFactory;
use Symfony\Component\Console\Input\InputArgument;
use Webman\Console\Commands\MakeModelCommand;
use Webman\Console\Util;

/**
 * 构建ThinkPHP模型类.
 */
class Builder extends MakeModelCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'make:m';

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Model name');
        $this->addArgument('type', InputArgument::OPTIONAL, 'Type', 'tp');
    }

    protected function createModel($class, $namespace, $file): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $table = Util::classToName($class);
        $table_val = 'null';
        $pk = 'id';
        $properties = '';

        try {
            $prefix = config('database.connections.mysql.prefix') ?? '';
            $database = config('database.connections.mysql.database');
            $inflector = InflectorFactory::create()->build();
            $table_plura = $inflector->pluralize($inflector->tableize($class));
            if (\support\Db::select("show tables like '{$prefix}{$table_plura}'")) {
                $table_val = "'{$table}'";
                $table = "{$prefix}{$table_plura}";
            } elseif (\support\Db::select("show tables like '{$prefix}{$table}'")) {
                $table_val = "'{$table}'";
                $table = "{$prefix}{$table}";
            }
            $tableComment = \support\Db::select('SELECT table_comment FROM information_schema.`TABLES` WHERE table_schema = ? AND table_name = ?', [$database, $table]);
            if (!empty($tableComment)) {
                $comments = $tableComment[0]->table_comment ?? $tableComment[0]->TABLE_COMMENT;
                $properties .= " * {$table} {$comments}" . PHP_EOL;
            }
            foreach (\support\Db::select("select COLUMN_NAME,DATA_TYPE,COLUMN_KEY,COLUMN_COMMENT from INFORMATION_SCHEMA.COLUMNS where table_name = '{$table}' and table_schema = '{$database}' ORDER BY ordinal_position") as $item) {
                if ('PRI' === $item->COLUMN_KEY) {
                    $pk = $item->COLUMN_NAME;
                    $item->COLUMN_COMMENT .= '(主键)';
                }
                $type = $this->getType($item->DATA_TYPE);
                $properties .= " * @property {$type} \${$item->COLUMN_NAME} {$item->COLUMN_COMMENT}\n";
            }
        } catch (\Throwable $e) {
        }
        $properties = rtrim($properties) ?: ' *';
        $model_content = <<<EOF
<?php

namespace {$namespace};

use support\\Model;

/**
{$properties}
 */
class {$class} extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected \$table = {$table_val};

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected \$primaryKey = '{$pk}';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public \$timestamps = false;
    
    
}

EOF;
        file_put_contents($file, $model_content);

        $trait_content = <<<EOF
<?php

namespace {$namespace};

/**
{$properties}
 */
trait Has{$class}
{
}

EOF;
        file_put_contents(dirname($file) . "/Has{$class}.php", $trait_content);
    }

    protected function createTpModel($class, $namespace, $file): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $table = Util::classToName($class);
        $table_val = 'null';
        $pk = 'id';
        $properties = '';

        try {
            $prefix = config('thinkorm.connections.mysql.prefix') ?? '';
            $database = config('thinkorm.connections.mysql.database');
            if (\think\facade\Db::query("show tables like '{$prefix}{$table}'")) {
                $table = "{$prefix}{$table}";
                $table_val = "'{$table}'";
            } elseif (\think\facade\Db::query("show tables like '{$prefix}{$table}s'")) {
                $table = "{$prefix}{$table}s";
                $table_val = "'{$table}'";
            }
            $tableComment = \think\facade\Db::query('SELECT table_comment FROM information_schema.`TABLES` WHERE table_schema = ? AND table_name = ?', [$database, $table]);
            if (!empty($tableComment)) {
                $comments = $tableComment[0]['table_comment'] ?? $tableComment[0]['TABLE_COMMENT'];
                $properties .= " * {$table} {$comments}" . PHP_EOL;
            }
            foreach (\think\facade\Db::query("select COLUMN_NAME,DATA_TYPE,COLUMN_KEY,COLUMN_COMMENT from INFORMATION_SCHEMA.COLUMNS where table_name = '{$table}' and table_schema = '{$database}' ORDER BY ordinal_position") as $item) {
                if ('PRI' === $item['COLUMN_KEY']) {
                    $pk = $item['COLUMN_NAME'];
                    $item['COLUMN_COMMENT'] .= '(主键)';
                }
                $type = $this->getType($item['DATA_TYPE']);
                $properties .= " * @property {$type} \${$item['COLUMN_NAME']} {$item['COLUMN_COMMENT']}\n";
            }
        } catch (\Throwable $e) {
        }
        $properties = rtrim($properties) ?: ' *';
        $model_content = <<<EOF
<?php

namespace {$namespace};

use think\\Model;

/**
{$properties}
 */
class {$class} extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected \$table = {$table_val};

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected \$pk = '{$pk}';

    
}

EOF;
        file_put_contents($file, $model_content);

        $trait_content = <<<EOF
<?php

namespace {$namespace};

/**
{$properties}
 */
trait Has{$class}
{
}

EOF;
        file_put_contents(dirname($file) . "/Has{$class}.php", $trait_content);
    }
}
