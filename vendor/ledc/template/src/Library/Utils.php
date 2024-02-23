<?php

namespace Ledc\Template\Library;

/**
 * 工具类.
 */
class Utils
{
    /**
     * 对布尔型进行格式化.
     *
     * @param mixed $value 变量值
     *
     * @return bool 格式化后的变量
     */
    public static function toBoolean(mixed $value): bool
    {
        return match (true) {
            is_bool($value) => $value,
            is_numeric($value) => $value > 0,
            is_string($value) => in_array(strtolower($value), ['ok', 'true', 'success', 'on', 'yes', '(ok)', '(true)', '(success)', '(on)', '(yes)']),
            is_array($value) => !empty($value),
            default => (bool)$value,
        };
    }

    /**
     * 转换成易读的容量格式(包含小数)
     * - 字节数Byte转换为KB、MB、GB、TB.
     *
     * @param float|int $bytes 字节
     * @param string $delimiter 分隔符 [&nbsp; | <br />]
     * @param int $decimals 保留小数点
     */
    public static function size(float|int $bytes, string $delimiter = '', int $decimals = 2): string
    {
        $type = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $i = 0;
        while ($bytes >= 1024) {
            $bytes /= 1024;
            ++$i;
        }

        return number_format($bytes, $decimals) . $delimiter . $type[$i];
    }

    /**
     * 持续时长转类似ISO8601中文时间格式.
     *
     * @param int $duration 持续时长（秒）
     */
    public static function toISO8601(int $duration): string
    {
        $time = $duration;
        $units = [
            '年' => 3600 * 24 * 365,
            '天' => 3600 * 24,
            '时' => 3600,
            '分' => 60,
            '秒' => 1,
        ];
        $rs = [];
        foreach ($units as $name => $unit) {
            if ($unit <= $duration) {
                $quot = intval($time / $unit);
                $time -= $quot * $unit;
                if ($quot && count($rs) < 3) {
                    $rs[] = $quot . $name;
                }
            }
        }

        return implode('', $rs);
    }

    /**
     * 将ISO 8601格式：PnYnMnDTnHnMnS，转换成秒.
     */
    public static function ISO8601ToSeconds(string $str = 'PT1H59M'): bool|float|int
    {
        try {
            $dv = new \DateInterval($str);

            return ($dv->y * 31536000) +
                ($dv->m * 2592000) +
                ($dv->d * 86400) +
                ($dv->h * 3600) +
                ($dv->i * 60) +
                $dv->s;
        } catch (\Exception $e) {
            return false;
        }
    }
}
