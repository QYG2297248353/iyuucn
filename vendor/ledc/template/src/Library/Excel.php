<?php

namespace Ledc\Template\Library;

/**
 * PHP-XlsWriter导出文件.
 */
class Excel
{
    /**
     * PHP-XlsWriter导出文件.
     *
     * @param string $path xlsx文件保存路径
     * @param string $filename 文件名
     * @param array $header 表头
     * @param array $data 表数据（二维数组）
     */
    public function writer(string $path, string $filename, array $header, array $data): string
    {
        try {
            $config = [
                'path' => $path,     // xlsx文件保存路径
            ];
            $excel = new \Vtiful\Kernel\Excel($config);

            // fileName 会自动创建一个工作表，你可以自定义该工作表名称，工作表名称为可选参数
            $filePath = $excel->fileName($filename . '.xlsx', 'sheet1')
                ->header($header)->data($data)->output();

            // 关闭当前打开的所有文件句柄 并 回收资源
            if (is_callable([$excel, 'close'], true)) {
                $excel->close();
            }

            return $filePath;
        } catch (\Throwable $throwable) {
            throw new \RuntimeException($throwable->getMessage(), $throwable->getCode());
        }
    }
}
