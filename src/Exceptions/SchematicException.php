<?php

namespace Assegai\Cli\Exceptions;

class SchematicException extends ConsoleException
{

  /**
   * @param string $message
   */
  public function __construct(string $message = 'Invalid schematic')
  {
    parent::__construct(message: $message);
  }
}