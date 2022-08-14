<?php

namespace Assegai\Cli\Exceptions;

class FileException extends ConsoleException
{
  public function __construct(string $message = "")
  {
    parent::__construct(sprintf("File exception, %s", $message));
  }
}