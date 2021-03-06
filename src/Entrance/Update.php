<?php

namespace Horseloft\Plodder\Entrance;

use Horseloft\Plodder\Builder\Core;
use Horseloft\Plodder\Builder\ConditionBuilder;
use Horseloft\Plodder\Builder\ExecuteBuilder;

/**
 * 必须调用execute()才会执行update
 *
 * Class Update
 * @package Horseloft\Plodder\Entrance
 */
class Update extends Core
{
    use ConditionBuilder,ExecuteBuilder;

    /**
     * @param string|array $connection
     * @param string $table
     * @param array $data
     */
    public function __construct($connection, string $table, array $data)
    {
        parent::__construct($connection, $table, self::UPDATE);

        $this->setItem = $data;
    }
}
