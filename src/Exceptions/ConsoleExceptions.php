<?php

namespace Assegai\Cli\Exceptions;

use Exception;

class ConsoleExceptions extends Exception
{
  public function __construct(string $message = "")
  {
    parent::__construct($message);
  }
}