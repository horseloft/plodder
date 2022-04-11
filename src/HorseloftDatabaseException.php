<?php

namespace Horseloft\Database;

use Throwable;

class HorseloftDatabaseException extends \RuntimeException
{
    public function __construct($message = "", $code = 6006, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
