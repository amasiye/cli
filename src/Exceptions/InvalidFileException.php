<?php

namespace Assegai\Cli\Exceptions;

class InvalidFileException extends ConsoleException
{
  /**
   * @param string $filename
   * @param string $reason
   */
  public function __construct(string $filename, string $reason)
  {
    parent::__construct(message: sprintf("%s is invalid. %s", $filename, $reason));
  }
}