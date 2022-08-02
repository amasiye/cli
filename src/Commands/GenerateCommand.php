<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Interfaces\IArgumentHost;

#[Command(
  name: 'generate',
  shortName: 'g',
  description: 'Generate an Assegai element.'
)]
class GenerateCommand extends AbstractCommand
{
  public function execute(IArgumentHost $context): int
  {
    return Command::SUCCESS;
  }
}