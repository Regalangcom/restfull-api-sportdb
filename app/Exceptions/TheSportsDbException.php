<?php

namespace App\Exceptions;

use Exception;

class TheSportsDbException extends Exception
{
    public static function unavailable(): self
    {
        return new self('The external sports data provider is currently unavailable.');
    }
}
