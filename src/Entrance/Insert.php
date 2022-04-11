<?php

namespace Horseloft\Database\Entrance;

use Horseloft\Database\Builder\Core;
use Horseloft\Database\Builder\ExecuteBuilder;

/**
 * 必须调用execute()才会执行insert
 *
 * Class Insert
 * @package Horseloft\Database\Entrance
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
