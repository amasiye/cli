<?php

namespace Assegai\Cli\Exceptions;

use Exception;

class ConsoleException extends Exception
{
  public function __construct(string $message = "")
  {
    parent::__construct($message);
  }
}