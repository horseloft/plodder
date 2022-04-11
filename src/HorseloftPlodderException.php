<?php

namespace Horseloft\Plodder;

use Throwable;

class HorseloftPlodderException extends \RuntimeException
{
    public function __construct($message = "", $code = 6006, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
