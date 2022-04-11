<?php

namespace Horseloft\Database\Builder;

trait ConditionBuilder
{
    /**
     * --------------------------------------------------------------------------
     * sql语句的where条件
     * --------------------------------------------------------------------------
     *
     * 支持一次查询中调用多次该方法，会自动拼接多次调用生成的where条件
     *
     * 格式：键 => 值
     * 例：
     *  ['name' => 'jack']                          //and name = 'jack'
     *  ['name' => 'eq' => 'jack']                  //and name = 'jack'
     *  ['name' => 'ne' => 'jack']              //and name != 'jack'
     *
     *  ['age' => ['gt' => 12]]                     //and age > 12
     *  ['age' => ['ge' => 12]]                    //and age >= 12
     *
     *  ['name' => ['like' => '%jack$']]            //and name like '%jack%'
     *
     *  ['age' => ['lt' => 12]]                     //and age < 12
     *  ['age' => ['le' => 12]]                    //and age <= 12
     *
     *  ['age' => [btw => [10, 20]]]            //and age between 10 and 20
     *
     *  ['home' => ['in' => ['china', 'us']]]       //and home in ('china', 'us')
     *  ['home' => ['nin' => ['china', 'us']]]   //and home not in ('china', 'us')
     *
     * @param array $where
     * @return $this
     */
    public function where(array $where = [])
    {
        if (!empty($where)) {
            array_push($this->whereItem, [
                'type' => 'and',
                'item' => $where
            ]);
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------
     * or 查询条件
     * --------------------------------------------------------------------------
     *
     * 如果生成器中没有使用where()方法 则whereOr()功能与where()方法一样
     *
     * 语法参考 where() 方法
     *
     * 生成的SQL语句：or (xxx = xxx and xxx = xxx and xxx like xxx)
     *
     * @param array $where
     * @return $this
     */
    public function whereOr(array $where = [])
    {
        if (!empty($where)) {
            array_push($this->whereItem, [
                'type' => 'or',
                'item' => $where
            ]);
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------
     *  原生的where SQL语句
     * --------------------------------------------------------------------------
     *
     * $raw 中无需写明where，$raw中以?作为占位符
     * $binding $raw语句中?占位符绑定的参数
     *
     * @param string $raw
     * @param array $binding
     * @return $this
     */
    public function whereRaw(string $raw = '', array $binding = [])
    {
        if ($raw != '') {
            array_push($this->whereItem, [
                'type' => 'raw',
                'item' => [
                    'raw' => $raw,
                    'binding' => $binding
                ]
            ]);
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------
     * group by 语句
     * --------------------------------------------------------------------------
     *
     * $column 会自动追加 group by
     *
     * @param string $column
     * @return $this
     */
    public function group(string $column = '')
    {
        if ($column != '') {
            $this->groupItem = $column;
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------
     * order by 语句
     * --------------------------------------------------------------------------
     *
     * $string 会自动追加 order by
     *
     * 例：id desc,username asc
     *
     * @param string $string
     * @return $this
     */
    public function order(string $string = '')
    {
        if ($string != '') {
            $this->orderItem = [
                'type' => 'order',
                'string' => $string
            ];
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------
     * order by 语句
     * --------------------------------------------------------------------------
     *
     * $column 会自动追加 order by desc
     *
     * @param string $column
     * @return $this
     */
    public function orderDesc(string $column = '')
    {
        if ($column != '') {
            $this->orderItem = [
                'type' => 'desc',
                'string' => $column
            ];
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------
     * order by 语句
     * --------------------------------------------------------------------------
     *
     * $column 会自动追加 order by asc
     *
     * @param string $column
     * @return $this
     */
    public function orderAsc(string $column = '')
    {
        if ($column != '') {
            $this->orderItem = [
                'type' => 'asc',
                'string' => $column
            ];
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------
     * limit 语句
     * --------------------------------------------------------------------------
     *
     * $limit 应 >= 0 | 如果 $limit = null 则置空SQL语句中的limit条件
     * $offset 应 > 0 | 如果 $offset= null 则仅查询$limit条数据
     *
     * @param int $limit
     * @param int|null $offset
     * @return $this
     */
    public function limit(int $limit, int $offset = null)
    {
        if ($limit < 0) {
            return $this;
        }

        $this->limitItem = [
            'limit' => $limit,
            'offset' => $offset
        ];
        return $this;
    }
}
