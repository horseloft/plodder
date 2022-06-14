<?php

namespace Horseloft\Plodder\Entrance;

use Horseloft\Plodder\Builder\Connection;
use Horseloft\Plodder\Builder\StatementBuilder;
use Horseloft\Plodder\HorseloftPlodderException;
use PDO;
use PDOStatement;

/**
 * 可以使用PDO原生语句
 *
 * Class DataObject
 * @package Horseloft\Plodder\Entrance
 */
class DatabaseObject
{
    use StatementBuilder;

    /**
     * @var PDO
     */
    protected $connect = '';

    /**
     * @var array
     */
    private $databaseConfig;

    /**
     * 配置连接项
     *
     * @param $connection
     *
     * @internal
     */
    public function setConnection($connection)
    {
        $this->databaseConfig = Connection::config($connection, 'PDO');
    }

    /**
     * ----------------------------------------------------------------
     * PDO::query()
     * ----------------------------------------------------------------
     *
     * select
     *
     * 返回 FETCH_ASSOC 结果集
     *
     * @param string $sql
     * @return PDOStatement
     */
    public function query(string $sql): PDOStatement
    {
        $sql = $this->getAndSet($sql);

        $this->connect = Connection::connect($this->databaseConfig);

        $stmt = $this->connect->query($sql, PDO::FETCH_ASSOC);

        if ($stmt == false) {
            throw new HorseloftPlodderException($this->connect->errorInfo()[2]);
        }
        return $stmt;
    }

    /**
     * ----------------------------------------------------------------
     * 返回一条记录
     * ----------------------------------------------------------------
     *
     * @param string $sql
     * @param string|int|float|null ...$param
     * @return array
     */
    public function fetch(string $sql, ...$param): array
    {
        $sql = $this->getAndSet($sql);
        $statement = $this->statement($sql, $param);

        return $this->fetchBuilder($statement, false);
    }

    /**
     * ----------------------------------------------------------------
     * 返回全部记录
     * ----------------------------------------------------------------
     *
     * @param string $sql
     * @param string|int|float|null ...$param
     * @return array
     */
    public function fetchAll(string $sql, ...$param): array
    {
        $sql = $this->getAndSet($sql);
        $statement = $this->statement($sql, $param);

        return $this->fetchBuilder($statement);
    }

    /**
     * ----------------------------------------------------------------
     * PDO::exec()
     * ----------------------------------------------------------------
     *
     * insert|delete|update
     *
     * delete|update:返回受影响的行数
     * insert:返回最后一个写入的ID
     *
     * @param string $sql
     * @param string|int|float|null ...$param
     * @return int
     */
    public function exec(string $sql, ...$param): int
    {
        $sql = $this->getAndSet($sql);

        $statement = $this->statement($sql, $param);

        if (strtolower(substr($sql, 0, 6)) == 'insert') {
            return (int)$this->connect->lastInsertId();
        }
        return $statement->rowCount();
    }

    /**
     * 最后插入行的ID或序列值
     *
     * @param null $column
     * @return string
     */
    public function lastInsertId($column = null): string
    {
        return $this->connect->lastInsertId($column);
    }

    /**
     * @param string $sql
     * @return string
     */
    private function getAndSet(string $sql): string
    {
        $sql = trim($sql);
        if (empty($sql)) {
            throw new HorseloftPlodderException('Parameter cannot be empty');
        }
        return $sql;
    }
}
