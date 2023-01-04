<?php

namespace Assegai\Cli\Exceptions;

class InvalidException extends ConsoleException
{
  public function __construct(string $schematicName)
  {
    $schematicName = str_replace('\\', '/', $schematicName);
    $schematicName = basename($schematicName);
    parent::__construct("Invalid schematic \"$schematicName\". Please, ensure that \"$schematicName\" exists in this collection.");
  }
}