<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\ActionContext;
use Assegai\Cli\Interfaces\IArgumentHost;

#[Command(
  name: 'help',
  usage: 'help [command_name]',
  shortName: 'h',
  description: 'Displays helpful information about a given command.'
)]
class HelpCommand extends AbstractCommand
{
  public function configure(): void
  {
    parent::configure();
    $this->addArgument(
      name: 'command',
      description: 'The name of the command for which '
    );
  }

  public function execute(ActionContext|IArgumentHost $context): int
  {
    if ($commandName = $this->getNextArgument($context->getArgs()))
    {
      if ($context->getApp()->has($commandName))
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