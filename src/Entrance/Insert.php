<?php

namespace Horseloft\Plodder\Entrance;

use Horseloft\Plodder\Builder\Core;
use Horseloft\Plodder\Builder\ExecuteBuilder;

/**
 * 必须调用execute()才会执行insert
 *
 * Class Insert
 * @package Horseloft\Plodder\Entrance
 */
class Insert extends Core
{
    use ExecuteBuilder;

    /**
     * @param string|array $connection
     * @param string $table
     * @param array $data
     */
    public function __construct($connection, string $table, array $data)
    {
        parent::__construct($connection, $table, self::INSERT);

        $this->insertItem = $data;
    }
}
