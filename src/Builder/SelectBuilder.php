<?php

namespace Horseloft\Plodder\Builder;

trait SelectBuilder
{
    /**
     * @param string $table
     * @param string $on
     * @return $this
     */
    public function join(string $table, string $on = '')
    {
        $this->setJoinSql('cross', $table, $on);
        return $this;
    }

    /**
     * @param string $table
     * @param string $on
     * @return $this
     */
    public function leftJoin(string $table, string $on = '')
    {
        $this->setJoinSql('left', $table, $on);
        return $this;
    }

    /**
     * @param string $table
     * @param string $on
     * @return $this
     */
    public function rightJoin(string $table, string $on = '')
    {
        $this->setJoinSql('right', $table, $on);
        return $this;
    }

    /**
     * @param string $table
     * @param string $on
     * @return $this
     */
    public function innerJoin(string $table, string $on = '')
    {
        $this->setJoinSql('inner', $table, $on);
        return $this;
    }

    /**
     * 查询一条
     *
     * limit优先级 first() > page() > limit()
     *
     * @return array
     */
    public function first(): array
    {
        $this->limitItem = [
            'limit' => $this->limitItem['limit'] ?? 0,
            'offset' => 1
        ];
        return $this->fetch(false);
    }

    /**
     * 查询全部
     *
     * @return array
     */
    public function all(): array
    {
        return $this->fetch(true);
    }

    /**
     * count()统计查询
     *
     * @return int
     */
    public function count(): int
    {
        $this->isCount = true;

        $data = $this->fetch(false);

        if (empty($data)) {
            return 0;
        } else {
            return (int)$data['num'];
        }
    }

    /**
     * --------------------------------------------------------------------------
     * 分页数据
     * --------------------------------------------------------------------------
     *
     * @param int $page
     * @param int $pageSize
     * @return $this
     */
    public function page(int $page = 1, int $pageSize = 20)
    {
        $start = 0;
        $size = 1;
        if ($pageSize > 0) {
            $size = $pageSize;
        }
        if ($page > 1) {
            $start = ($page - 1) * $size;
        }

        $this->limitItem = [
            'limit' => $start,
            'offset' => $size
        ];
        return $this;
    }

    /**
     * 数据查询
     *
     * @param bool $isFetchAll
     * @return array
     */
    private function fetch(bool $isFetchAll): array
    {
        $statement = $this->statement($this->toSql(), $this->getSqlParam());

        return $this->fetchBuilder($statement, $isFetchAll);
    }

    /**
     * @param string $type
     * @param string $table
     * @param string $on
     */
    private function setJoinSql(string $type, string $table, string $on)
    {
        array_push($this->joinItem, [
            'type' => $type,
            'table' => $table,
            'on' => $on
        ]);
    }
}
