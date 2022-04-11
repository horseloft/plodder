<?php

namespace Horseloft\Database\Entrance;

use Horseloft\Database\Builder\Core;
use Horseloft\Database\Builder\ExecuteBuilder;
use Horseloft\Database\Builder\ConditionBuilder;

/**
 * 必须调用execute()才会执行delete
 *
 * Class Delete
 * @package Horseloft\Database\Entrance
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