<?php

namespace Assegai\Cli\Interfaces;

interface IActionHandler
{
  public function handle(string $action, IExecutionContext $context): int;
}