<?php

namespace Ledc\Template\Model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id (主键)
 * @property string $ip 访问ip
 * @property string $uri 访问uri
 * @property string $method 请求方法
 * @property string $appid 应用平台appid
 * @property string $trace_id trace_id
 * @property string $referer 来源页
 * @property string $user_agent user_agent
 * @property string $query 请求参数
 * @property string $cookie 请求cookie
 * @property string $errcode 响应错误码
 * @property string $response 响应结果
 * @property string $exception 异常信息
 * @property string $exec_time 执行时间，单位毫秒
 * @property string $created_at 创建时间
 */
class GlobalLog extends Base
{
    /**
     * 数据表后缀
     * @var string
     */
    public static string $tableSuffix = '';

    /**
     * 数据表名
     */
    protected const TABLE_NAME = 'global_log';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 构造函数
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (empty(static::$tableSuffix)) {
            static::$tableSuffix = static::builderTableSuffix(date('Ymd'));
        }
        $this->setTable(self::TABLE_NAME . static::$tableSuffix);
        parent::__construct($attributes);
    }

    /**
     * 切换后缀进行查询
     * @param string $suffix
     * @return self
     */
    final public static function tableSuffix(string $suffix): self
    {
        static::$tableSuffix = static::builderTableSuffix($suffix);
        return new static();
    }

    /**
     * 拼接表后缀
     * @param string $suffix
     * @return string
     */
    public static function builderTableSuffix(string $suffix): string
    {
        return '_' . $suffix;
    }
}
