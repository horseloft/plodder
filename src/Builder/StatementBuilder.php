<?php

namespace Horseloft\Plodder\Builder;

use Horseloft\Plodder\HorseloftPlodderException;
use PDO;
use PDOStatement;
use Throwable;

trait StatementBuilder
{
    /**
     * SQL预处理 SQL参数绑定
     *
     * @param string $sql
     * @param array $param
     * @return PDOStatement
     */
    protected function statement(string $sql, array $param = []): PDOStatement
    {
        $this->connect = Connection::connect($this->databaseConfig);
        try {
            //SQL预处理
            $stmt = $this->connect->prepare($sql);
            if ($stmt == false) {
                throw new HorseloftPlodderException($stmt->errorInfo()[2]);
            }

            //绑定参数
            $inc = 1;
            foreach ($param as $value) {
                if ($stmt->bindValue($inc, $value) == false) {
                    throw new HorseloftPlodderException($stmt->errorInfo()[2]);
                }
                $inc++;
            }

            //执行动作
            if ($stmt->execute() == false) {
                throw new HorseloftPlodderException($stmt->errorInfo()[2]);
            }

            //返回 \PDOStatement
            return $stmt;

        } catch (Throwable $e) {
            throw new HorseloftPlodderException($e->getMessage());
        }
    }

    /**
     * PDO fetch查询
     *
     * @param PDOStatement $statement
     * @param bool $isFetchAll
     * @return array
     */
    protected function fetchBuilder(PDOStatement $statement, bool $isFetchAll = true): array
    {
        if ($isFetchAll) {
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        }
        if ($result == false) {
            return [];
        }
        return $result;
    }
}