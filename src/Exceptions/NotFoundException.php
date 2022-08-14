<?php

namespace Assegai\Cli\Exceptions;

class NotFoundException extends ConsoleException
{
  public function __construct(string $message)
  {
    parent::__construct("Not Found - " . $message);
  }
}