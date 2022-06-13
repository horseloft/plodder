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
     *
     * @internal
     */
    public function init($connection, string $table)
    {
        parent::initialize($connection, $table, self::DELETE);
    }
}
