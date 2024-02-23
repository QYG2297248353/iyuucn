<?php

namespace Ledc\Template;

/**
 * 安装脚本.
 */
class Install
{
    /** @var bool */
    public const WEBMAN_PLUGIN = true;

    /**
     * 初次安装时需要覆盖的文件.
     */
    public const overwrite = [
        'app/model/WaUserObserver.stub' => 'app/model/WaUserObserver.php',
        'app/Bootstrap.stub' => 'app/Bootstrap.php',
        'app/ExceptionHandler.stub' => 'app/ExceptionHandler.php',
        'config/database.php' => 'config/database.php',
        'config/exception.php' => 'config/exception.php',
        'config/gateway_worker.php' => 'config/gateway_worker.php',
        'config/middleware.php' => 'config/middleware.php',
        'config/thinkorm.php' => 'config/thinkorm.php',
        'support/Request.stub' => 'support/Request.php',
        'gateway_worker.stub' => 'gateway_worker.php',
        '.example.env' => '.example.env',
        'clear.sh' => 'clear.sh',
        'gg.sh' => 'gg.sh',
        'restart.sh' => 'restart.sh',
    ];

    protected static array $pathRelation = [
        'config/plugin/ledc/template' => 'config/plugin/ledc/template',
    ];

    /**
     * Install.
     */
    public static function install(): void
    {
        $lock = config_path('plugin/ledc/template/app.php');
        if (!is_file($lock)) {
            foreach (static::overwrite as $source => $dest) {
                if ($pos = strrpos($dest, '/')) {
                    $parent_dir = base_path() . '/' . substr($dest, 0, $pos);
                    if (!is_dir($parent_dir)) {
                        mkdir($parent_dir, 0777, true);
                    }
                }

                self::copy_dir(__DIR__ . "/{$source}", base_path() . "/{$dest}");
                echo "Create {$dest}" . PHP_EOL;
            }
        }

        static::installByRelation();
    }

    /**
     * 【强制覆盖】复制目录 Copy dir.
     */
    public static function copy_dir(string $source, string $dest): void
    {
        if (is_dir($source)) {
            if (!is_dir($dest)) {
                mkdir($dest);
            }
            $files = scandir($source);
            foreach ($files as $file) {
                if ('.' !== $file && '..' !== $file) {
                    self::copy_dir("{$source}/{$file}", "{$dest}/{$file}");
                }
            }
        } elseif (file_exists($source)) {
            copy($source, $dest);
        }
    }

    /**
     * installByRelation.
     */
    public static function installByRelation(): void
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path() . '/' . substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0777, true);
                }
            }
            // symlink(__DIR__ . "/$source", base_path()."/$dest");
            copy_dir(__DIR__ . "/{$source}", base_path() . "/{$dest}");
            echo "Create {$dest}" . PHP_EOL;
        }
    }

    /**
     * Uninstall.
     */
    public static function uninstall(): void
    {
        self::uninstallByRelation();
    }

    /**
     * uninstallByRelation.
     */
    public static function uninstallByRelation(): void
    {
        foreach (static::$pathRelation as $source => $dest) {
            $path = base_path() . "/{$dest}";
            if (!is_dir($path) && !is_file($path)) {
                continue;
            }
            echo "Remove {$dest}" . PHP_EOL;
            if (is_file($path) || is_link($path)) {
                unlink($path);

                continue;
            }
            remove_dir($path);
        }
    }
}
