<?php

namespace Assegai\Cli\Interfaces;

use Assegai\Cli\Enumerations\ContextType;

interface IArgumentHost
{
  public function getType(): ContextType;

  public function getArgs(): array;

  public function getArgsById(int $id): array;
}