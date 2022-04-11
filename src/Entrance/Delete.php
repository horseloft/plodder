<?php

namespace Horseloft\Plodder\Entrance;

use Horseloft\Plodder\Builder\Core;
use Horseloft\Plodder\Builder\ExecuteBuilder;
use Horseloft\Plodder\Builder\ConditionBuilder;

/**
 * 必须调用execute()才会执行delete
 *
 * Class Delete
 * @package Horseloft\Plodder\Entrance
 */
class Delete extends Core
{
    use ConditionBuilder,ExecuteBuilder;

    /**
     * @param string|array $connection
     * @param string $table
     */
    public function __construct($connection, string $table)
    {
        parent::__construct($connection, $table, self::DELETE);
    }
}