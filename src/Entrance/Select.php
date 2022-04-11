<?php

namespace Horseloft\Database\Entrance;

use Horseloft\Database\Builder\Core;
use Horseloft\Database\Builder\ConditionBuilder;
use Horseloft\Database\Builder\SelectBuilder;

/**
 * 必须调用first()/all()/count()才会执行select
 *
 * Class Select
 * @package Horseloft\Database\Entrance
 */
class Select extends Core
{
    use ConditionBuilder,SelectBuilder;

    /**
     * @param string|array $connection
     * @param string $table
     * @param string $column
     */
    public function __construct($connection, string $table, string $column)
    {
        parent::__construct($connection, $table, self::SELECT);

        $this->column = $column;
    }
}
