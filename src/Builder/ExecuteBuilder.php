<?php

namespace Horseloft\Plodder\Builder;

trait ExecuteBuilder
{
    /**
     * --------------------------------------------------------
     * 执行删除操作
     * --------------------------------------------------------
     *
     * 成功返回受影响的行 失败返回false
     *
     * 如果未删除 则返回0
     *
     * 使用 === false 判断失败
     *
     * @return int
     */
    public function execute()
    {
        $statement = $this->statement($this->toSql(), $this->getSqlParam());

        if (isset($this->isSimpleInsert) && $this->isSimpleInsert == true) {
            return $this->connect->lastInsertId();
        }
        return $statement->rowCount();
    }
}