<?php

namespace Horseloft\Plodder\Entrance;

use Horseloft\Plodder\Builder\Core;
use Horseloft\Plodder\Builder\ConditionBuilder;
use Horseloft\Plodder\Builder\SelectBuilder;

/**
 * 必须调用first()/all()/count()才会执行select
 *
 * Class Select
 * @package Horseloft\Plodder\Entrance
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
