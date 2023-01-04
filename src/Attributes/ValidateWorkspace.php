<?php

namespace Assegai\Cli\Attributes;

use Assegai\Cli\Exceptions\ConsoleException;
use Assegai\Cli\Util\Paths;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidateWorkspace
{
  /**
   * @throws ConsoleException
   */
  public function __construct()
  {
    if (! file_exists(Paths::getAssegaiJsonConfigPath()) )
    {
      throw new ConsoleException("This command is not available when running the Assegai CLI outside a workspace.");
    }
  }
}