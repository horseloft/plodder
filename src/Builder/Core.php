<?php

namespace Horseloft\Plodder\Builder;

use Horseloft\Plodder\HorseloftPlodderException;
use PDO;

class Core
{
    use StatementBuilder;

    /**
     * 数据量连接的配置信息
     * @var array
     */
    private $databaseConfig;

    /**
     * @var PDO
     */
    protected $connect = null;

    protected $driver = '';

    protected $table = '';

    protected $column = '*';

    protected $whereSql = '';

    protected $whereParam = [];

    protected $insertSql = '';

    protected $orParam = [];

    protected $setParam = [];

    protected $header = '';

    protected $leftSign = '[';

    protected $rightSign = ']';

    protected const UPDATE = 'UPDATE';

    protected const SELECT = 'SELECT';

    protected const DELETE = 'DELETE';

    protected const INSERT = 'INSERT';

    protected $limitItem = [];

    protected $joinItem = [];

    protected $whereItem = [];

    protected $groupItem = '';

    protected $orderItem = [];

    protected $setItem = [];

    protected $insertItem = [];

    protected $isSimpleInsert = false;

    protected $isCount = false;

    /**
     * @param string|array $connection
     * @param string $table
     * @param string $type
     *
     * @internal
     */
    public function initialize($connection, string $table, string $type)
    {
        $this->table = $table;

        $this->header = $type;

        // 获取数组格式的连接配置信息
        $this->databaseConfig = Connection::config($connection, $type);

        $this->driver = $this->databaseConfig['driver'];

        if ($this->driver == 'mysql') {
            $this->leftSign = '`';
            $this->rightSign = '`';
        }
    }

    /**
     * -----------------------------------------------------------
     * 设置数据表名称
     * -----------------------------------------------------------
     *
     * @param string $table
     * @return $this
     */
    public function table(string $table): Core
    {
        $this->table = $table;

        return $this;
    }

    /**
     * 带参数的SQL语句
     *
     * @return string
     */
    public function toCompleteSql(): string
    {
        $string = $this->toSql();
        $param = $this->getSqlParam();
        foreach ($param as $value) {
            $start = substr($string, 0, strpos($string, '?') + 1);
            $string = str_replace('?', "'" . $value . "'", $start) . substr($string, strpos($string, '?') + 1);
        }
        return $string;
    }

    /**
     * SQL语句
     * @return string
     */
    public function toSql(): string
    {
        switch($this->header) {
            case self::SELECT:
                if ($this->isCount) {
                    $string = 'select count(1) as num from ' . $this->packageColumn($this->table);
                } else {
                    $string = 'select ' . $this->packageSelectColumn($this->column) . ' from ' . $this->packageColumn($this->table);
                }
                break;
            case self::UPDATE:
                $string = 'update ' . $this->packageColumn($this->table) . $this->setQueryBuilder($this->setItem);
                break;
            case self::DELETE:
                $string = 'delete from ' . $this->packageColumn($this->table);
                break;
            case self::INSERT:
                $this->insertAnalyze($this->insertItem);
                $string = 'insert into ' . $this->packageColumn($this->table) . $this->insertSql;
                break;
            default:
                throw new HorseloftPlodderException('Unsupported operation:' . $this->header);
        }

        // join
        $string .= $this->joinItemBuilder();

        //非insert操作 会用到where、limit、group by等操作
        if ($this->header != self::INSERT) {
            // where
            foreach ($this->whereItem as $where) {
                $this->whereBuilder($where['item'], $where['type']);
            }
            $string .= $this->whereSql;

            //group by
            if ($this->groupItem != '') {
                $string .= ' group by ' . $this->packageColumn($this->groupItem);
            }

            //order by
            if (!empty($this->orderItem)) {
                $order = $this->orderItem['type'] == 'order' ? '' : ' ' . $this->orderItem['type'];
                $string .= ' order by ' . $this->orderItem['string'] . $order;
            }

            //SQLServer order by
            if (empty($this->orderItem) && $this->driver == 'sqlserver' && $this->header == self::SELECT) {
                $string .= ' order by 1';
            }

            // limit
            if (!empty($this->limitItem)) {
                if ($this->driver == 'mysql') {
                    $string .= ' limit ' . $this->limitItem['limit'];
                    if (!is_null($this->limitItem['offset'])) {
                        $string .= ',' . $this->limitItem['offset'];
                    }
                } else {
                    if (is_null($this->limitItem['offset'])) {
                        $limit = 0;
                        $offset = $this->limitItem['limit'];
                    } else {
                        $limit = $this->limitItem['limit'];
                        $offset = $this->limitItem['offset'];
                    }
                    $string .= ' offset ' . $limit . ' rows fetch next ' . $offset . ' rows only';
                }
            }
        }
        return $string;
    }

    /**
     * SQL语句的参数
     *
     * @return array
     */
    protected function getSqlParam(): array
    {
        return array_merge($this->setParam, $this->whereParam);
    }

    /**
     * 用转义符号包裹参数
     *
     * @param string $column
     * @return string
     */
    protected function packageColumn(string $column): string
    {
        return $this->leftSign . $column . $this->rightSign;
    }

    /**
     * 查询的SQL字段添加转义符号
     *
     * @param string $column
     * @return string
     */
    protected function packageSelectColumn(string $column): string
    {
        if (empty($column) || $column == '*') {
            return $column;
        }
        $columnStr = '';
        $columnList = explode(',', $column);
        foreach ($columnList as $col) {
            if (strpos($col, ' as ')) {
                $colList = explode(' as ', $col);
                $columnStr .= $this->packageColumn($colList[0]) . ' as ' . $this->packageColumn($colList[1]) . ',';
                continue;
            }
            if (strpos($col, ' ')) {
                $colList = explode(' ', $col);
                $columnStr .= $this->packageColumn($colList[0]) . ' ' . $this->packageColumn($colList[1]) . ',';
                continue;
            }
            $columnStr .= $this->packageColumn($col) . ',';
        }
        return rtrim($columnStr, ',');
    }

    /**
     * join语句整理
     *
     * @return string
     */
    private function joinItemBuilder(): string
    {
        $str = '';
        foreach ($this->joinItem as $item) {
            $str .= $item['type'] . ' join ' . $this->packageColumn($item['table']) . ' ';
            if (!empty($item['on'])) {
                $str .= 'on ' . $item['on'] . ' ';
            }
        }
        return empty($str) ? '' : ' ' . $str;
    }

    /**
     * update set语句
     *
     * @param array $data
     * @return string
     */
    private function setQueryBuilder(array $data): string
    {
        if (empty($data)) {
            throw new HorseloftPlodderException('Empty update');
        }
        $arr = [];
        $str = ' set ';
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $str .= $this->packageColumn($key) . ' = ?,';
            } else {
                throw new HorseloftPlodderException('Unsupported column:' . $key);
            }
            if (is_string($value) || is_numeric($value) || is_null($value)) {
                $arr[] = $value;
            } else {
                throw new HorseloftPlodderException('Unsupported column:' . $key);
            }
        }
        $this->setParam = $arr;
        return rtrim($str, ',');
    }


    /**
     * 如果$data是二维数组 使用批量写入
     *
     * @param array $data
     */
    private function insertAnalyze(array $data)
    {
        if (empty($data)) {
            throw new HorseloftPlodderException('data not allowed to be empty');
        }
        if (is_numeric(array_keys($data)[0]) && is_array($data[array_keys($data)[0]])) {
            //如果数组的第一个下标是数字，判定参数是二维数组
            $this->insertCollector($data);
            $this->isSimpleInsert = false;
        } else {
            $builder = $this->insertExplode($data);

            $this->insertSql = $builder['sql'];

            $this->whereParam = $builder['param'];

            $this->isSimpleInsert = true;
        }
    }

    /**
     * --------------------------------------------------------------
     *  写入多条数据
     * --------------------------------------------------------------
     *
     * $data 是二维数组:
     * [
     *      [
     *          键 => 值 //键为数据库字段，值为字段的值
     *      ]
     * ]
     *
     * @param array $data
     */
    private function insertCollector(array $data)
    {
        foreach ($data as $key => $value) {
            if (empty($value)) {
                throw new HorseloftPlodderException(' data not allowed to be empty');
            }

            if ($key == 0) {
                $isNeedColumn = true;
            } else {
                $isNeedColumn = false;
            }
            $builder = $this->insertExplode($value, $isNeedColumn);

            if ($this->insertSql == '') {
                $this->insertSql = $builder['sql'];
            } else {
                $this->insertSql .= ',' . $builder['sql'];
            }
            $this->whereParam = array_merge($this->whereParam, $builder['param']);
        }
    }

    /**
     * @param array $insert
     * @param bool $isNeedColumn
     * @return array
     */
    private function insertExplode(array $insert, bool $isNeedColumn = true): array
    {
        if ($isNeedColumn) {
            $columns = ' values (';
            $fields = ' (';
        } else {
            $columns = '(';
        }

        $params = [];
        foreach ($insert as $key => $value) {
            if (!is_string($key)) {
                throw new HorseloftPlodderException('Unsupported column:' . $key);
            }

            if (is_string($value) || is_numeric($value) || is_null($value)) {
                $columns .= '?,';
                $params[] = $value;
            } else {
                throw new HorseloftPlodderException('Unsupported column:' . $key);
            }

            if ($isNeedColumn) {
                $fields .= $this->packageColumn($key) . ',';
            }
        }

        if ($isNeedColumn) {
            $string = rtrim($fields, ',') . ')' . rtrim($columns, ',') . ')';
        } else {
            $string = rtrim($columns, ',') . ')';
        }

        return [
            'sql' => $string,
            'param' => $params
        ];
    }

    /**
     * * where条件拼接
     *
     * key不支持table.filed格式
     *
     * @param array $where
     * @param string $type
     */
    private function whereBuilder(array $where, string $type)
    {
        if ($type == 'raw') {
            if ($this->whereSql == '') {
                $this->whereSql = ' where ' . $where['raw'];
            } else {
                $this->whereSql .= ' ' . $where['raw'];
            }
            $this->whereParam = array_merge($this->whereParam, $where['binding']);
            return;
        }

        $param = [];
        $sql = '';
        foreach ($where as $key => $value) {
            if (is_array($value)) {
                if (key($value) == 'string') {
                    if (!is_string(end($value))) {
                        throw new HorseloftPlodderException('Unsupported column value');
                    }
                    $sql .= $this->packageColumn($key) . ' ' . end($value) . ' and ';
                } else {
                    $convert = $this->convert($value);
                    $sql .= $this->packageColumn($key) . $convert['sign'] . ' and ';
                    if (is_array($convert['value'])) {
                        foreach ($convert['value'] as $val) {
                            $param[] = $val;
                        }
                    } else {
                        $param[] = $convert['value'];
                    }
                }
            } else {
                $param[] = $value;
                $sql .= $this->packageColumn($key) . ' = ? and ';
            }
        }
        $sqlString = '(' . rtrim($sql, 'and ') . ')';

        if ($this->whereSql == '') {
            $this->whereSql = ' where ' . $sqlString;
        } else {
            $this->whereSql .= ' ' . $type . ' ' . $sqlString;
        }

        $this->whereParam = array_merge($this->whereParam, $param);
    }

    /**
     * 实体符号转SQL符号
     *
     * eq   =
     * ne   <>
     * gt   >
     * ge   >=
     * lt   <
     * le   <=
     * in   in
     * nin   not in
     * btw   between
     * like like
     *
     * @param array $condition
     * @return array
     */
    private function convert(array $condition): array
    {
        $content = current($condition);
        if ($content === false) {
            throw new HorseloftPlodderException('Unsupported query method');
        }
        if (is_array($content) && count($content) == 0) {
            throw new HorseloftPlodderException('Unsupported query method');
        }

        switch (key($condition)) {
            case 'eq':
                $result = [
                    'sign' => ' = ?',
                    'value' => $content
                ];
                break;
            case 'ne':
                $result = [
                    'sign' => ' <> ?',
                    'value' => $content
                ];
                break;
            case 'gt':
                $result = [
                    'sign' => ' > ?',
                    'value' => $content
                ];
                break;
            case 'ge':
                $result = [
                    'sign' => ' >= ?',
                    'value' => $content
                ];
                break;
            case 'lt':
                $result = [
                    'sign' => ' < ?',
                    'value' => $content
                ];
                break;
            case 'le':
                $result = [
                    'sign' => ' <= ?',
                    'value' => $content
                ];
                break;
            case 'like':
                $result = [
                    'sign' => ' like ?',
                    'value' => $content
                ];
                break;
            case 'btw':
                $result = [
                    'sign' => ' between ? and ?',
                    'value' => [
                        current($condition['btw']),
                        end($condition['btw'])
                    ]
                ];
                break;
            case 'in':
                $str = ' in (';
                $inCount = count($condition['in']);
                $str .= str_repeat('?,', $inCount);
                $result = [
                    'sign' => rtrim($str, ',') . ')',
                    'value' => $condition['in']
                ];
                break;
            case 'nin':
                $str = ' not in (';
                $inCount = count($condition['nin']);
                $str .= str_repeat('?,', $inCount);
                $result = [
                    'sign' => rtrim($str, ',') . ')',
                    'value' => $condition['nin']
                ];
                break;
            default:
                throw new HorseloftPlodderException('Unsupported query method : ' . key($condition));
        }
        return $result;
    }
}
