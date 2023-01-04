<?php

namespace Assegai\Cli\Exceptions;

class InvalidSchemaException extends SchematicException
{
  public function __construct(string $reason)
  {
    parent::__construct('Invalid schema, ' . $reason);
  }
}