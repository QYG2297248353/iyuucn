<?php

namespace Ledc\Template\Library;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use plugin\admin\app\common\Tree;
use plugin\admin\app\common\Util;
use support\exception\BusinessException;
use support\Model;
use support\Request;
use support\Response;

/**
 * CRUD之列表.
 */
trait HasSelect
{
    /**
     * 查询.
     *
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order);

        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 查询前置.
     *
     * @throws BusinessException
     */
    protected function selectInput(Request $request): array
    {
        $field = $request->get('field');
        $order = $request->get('order', 'asc');
        $format = $request->get('format', 'normal');
        $limit = (int)$request->get('limit', 'tree' === $format ? 1000 : 10);
        $limit = $limit <= 0 ? 10 : $limit;
        $order = 'asc' === $order ? 'asc' : 'desc';
        $where = $request->get();
        $page = (int)$request->get('page');
        $page = $page > 0 ? $page : 1;
        $table = config('plugin.admin.database.connections.mysql.prefix') . $this->getModel()->getTable();

        $allow_column = Util::db()->select("desc `{$table}`");
        if (!$allow_column) {
            throw new BusinessException('表不存在');
        }
        $allow_column = array_column($allow_column, 'Field', 'Field');
        if (!in_array($field, $allow_column)) {
            $field = null;
        }
        foreach ($where as $column => $value) {
            if (
                '' === $value || !isset($allow_column[$column])
                || is_array($value) && (empty($value) || !in_array($value[0], ['null', 'not null']) && !isset($value[1]))
            ) {
                unset($where[$column]);
            }
        }

        return [$where, $format, $limit, $field, $order, $page];
    }

    /**
     * 数据模型.
     */
    abstract protected function getModel(): ?Model;

    /**
     * 指定查询where条件,并没有真正的查询数据库操作.
     */
    protected function doSelect(array $where, string $field = null, string $order = 'desc'): EloquentBuilder|Model|QueryBuilder
    {
        $model = $this->getModel();
        foreach ($where as $column => $value) {
            if (is_array($value)) {
                if ('like' === $value[0] || 'not like' === $value[0]) {
                    $model = $model->where($column, $value[0], "%{$value[1]}%");
                } elseif (in_array($value[0], ['>', '=', '<', '<>'])) {
                    $model = $model->where($column, $value[0], $value[1]);
                } elseif ('in' == $value[0] && !empty($value[1])) {
                    $valArr = $value[1];
                    if (is_string($value[1])) {
                        $valArr = explode(',', trim($value[1]));
                    }
                    $model = $model->whereIn($column, $valArr);
                } elseif ('not in' == $value[0] && !empty($value[1])) {
                    $valArr = $value[1];
                    if (is_string($value[1])) {
                        $valArr = explode(',', trim($value[1]));
                    }
                    $model = $model->whereNotIn($column, $valArr);
                } elseif ('null' == $value[0]) {
                    $model = $model->whereNull($column);
                } elseif ('not null' == $value[0]) {
                    $model = $model->whereNotNull($column);
                } elseif ('' !== $value[0] || '' !== $value[1]) {
                    $model = $model->whereBetween($column, $value);
                }
            } else {
                $model = $model->where($column, $value);
            }
        }
        if ($field) {
            $model = $model->orderBy($field, $order);
        }

        return $model;
    }

    /**
     * 执行真正查询，并返回格式化数据.
     *
     * @param mixed $query
     * @param mixed $format
     * @param mixed $limit
     */
    protected function doFormat($query, $format, $limit): Response
    {
        $methods = [
            'select' => 'formatSelect',
            'tree' => 'formatTree',
            'table_tree' => 'formatTableTree',
            'normal' => 'formatNormal',
        ];
        $paginator = $query->paginate($limit);
        $total = $paginator->total();
        $items = $paginator->items();
        $format_function = $methods[$format] ?? 'formatNormal';

        return call_user_func([$this, $format_function], $items, $total);
    }

    /**
     * 格式化树.
     *
     * @param mixed $items
     */
    protected function formatTree($items): Response
    {
        $format_items = [];
        foreach ($items as $item) {
            $format_items[] = [
                'name' => $item->title ?? $item->name ?? $item->id,
                'value' => (string)$item->id,
                'id' => $item->id,
                'pid' => $item->pid,
            ];
        }
        $tree = new Tree($format_items);

        return $this->json($this->getSuccessCode(), 'ok', $tree->getTree());
    }

    /**
     * 响应.
     *
     * @param int $code 响应码
     * @param string $msg 消息
     * @param array $data 数据
     */
    abstract protected function json(int $code, string $msg = 'ok', array $data = []): Response;

    /**
     * 成功时候的响应码
     */
    abstract protected function getSuccessCode(): int;

    /**
     * 格式化表格树.
     *
     * @param mixed $items
     */
    protected function formatTableTree($items): Response
    {
        $tree = new Tree($items);

        return $this->json($this->getSuccessCode(), 'ok', $tree->getTree());
    }

    /**
     * 格式化下拉列表.
     *
     * @param mixed $items
     */
    protected function formatSelect($items): Response
    {
        if (method_exists($this, 'formatSelectConfig')) {
            [$name_column, $value_column] = $this->formatSelectConfig();
        } else {
            [$name_column, $value_column] = [null, null];
        }

        $formatted_items = [];
        foreach ($items as $item) {
            if (is_string($name_column)) {
                $name = $item->{$name_column} ?? $item->name ?? $item->id;
            } else {
                $name = $name_column instanceof \Closure ? $name_column($item) : ($item->name ?? $item->id);
            }
            $formatted_items[] = [
                'name' => $name,
                'value' => $value_column ? ($item->{$value_column} ?? $item->id) : $item->id,
            ];
        }

        return $this->json($this->getSuccessCode(), 'ok', $formatted_items);
    }

    /**
     * 通用格式化.
     *
     * @param mixed $items
     * @param mixed $total
     */
    protected function formatNormal($items, $total): Response
    {
        return json(['code' => $this->getSuccessCode(), 'msg' => 'ok', 'count' => $total, 'data' => $items]);
    }
}
