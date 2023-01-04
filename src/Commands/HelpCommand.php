<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\ActionContext;
use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Interfaces\IArgumentHost;

#[Command(
  name: 'help',
  shortName: 'h',
  description: 'Displays helpful information about a given command.',
  arguments: [
    new CommandArgument(name: 'command', description: 'The name of the command.')
  ]
)]
class HelpCommand extends AbstractCommand
{
  public function execute(ActionContext|IArgumentHost $context): int
  {
    if ($commandName = $this->getNextArgument($context->getArgs()))
    {
      if ($context->getApp()->hasCommand($commandName))
      {
        $command = $context->getApp()->getCommands()[$commandName];
        $command->help();
      }
    }
    else
    {
      $this->help();
    }

    return Command::SUCCESS;
  }
}