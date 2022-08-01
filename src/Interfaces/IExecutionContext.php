<?php

namespace Assegai\Cli\Interfaces;

use Assegai\Cli\Core\App;

interface IExecutionContext extends IArgumentHost
{
  public function getApp(): App;
}