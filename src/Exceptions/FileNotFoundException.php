<?php

namespace Assegai\Cli\Exceptions;

class FileNotFoundException extends NotFoundException
{
  /**
   * @param string $filename
   */
  public function __construct(string $filename)
  {
    parent::__construct(sprintf("File not found - %s", $filename));
  }
}