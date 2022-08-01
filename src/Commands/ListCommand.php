<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IExecutionContext;

#[Command(
  name: 'list',
  usage: 'list',
  shortName: 'l',
  description: 'Prints a list of available commands'
)]
class ListCommand extends AbstractCommand
{
  public function execute(IArgumentHost|IExecutionContext $context): int
  {
    printf("%sAvailable commands:%s\n", Color::YELLOW, Color::RESET);
    /** @var AbstractCommand[] $registeredCommands */
    $registeredCommands = $context->getApp()->getCommands();

    foreach ($registeredCommands as $command)
    {
      $name = $command->shortName
        ? sprintf("%s, %s", $command->name, $command->shortName)
        : $command->name;
      printf("  %-20s %s" . PHP_EOL, $name, $command->description);
    }

    return Command::SUCCESS;
  }
}