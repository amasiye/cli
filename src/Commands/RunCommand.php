<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Core\CommandOption;
use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Enumerations\ValueType;
use Assegai\Cli\Interfaces\IArgumentHost;

#[Command(
  name: 'run',
  usage: 'run [options] [--] [<script> [<args>...]]',
  description: 'Runs the scripts defined in composer.json',
  options: [
    new CommandOption(
      name: 'timeout',
      type: ValueRequirementType::REQUIRED,
      description: 'Sets script timeout in seconds, or 0 for never',
      valueType: ValueType::INTEGER,
      defaultValue: 0
    ),
    new CommandOption(
      name: 'dev',
      type: ValueRequirementType::NOT_ALLOWED,
      description: 'Sets the dev mode',
    ),
    new CommandOption(
      name: 'no-dev',
      type: ValueRequirementType::NOT_ALLOWED,
      description: 'Disables the dev mode',
    ),
    new CommandOption(
      name: 'list',
      alias: 'l',
      type: ValueRequirementType::NOT_ALLOWED,
      description: 'List scripts',
    ),
    new CommandOption(
      name: 'quiet',
      alias: 'q',
      type: ValueRequirementType::NOT_ALLOWED,
      description: 'Do not output any message',
    ),
  ],
  arguments: [
    new CommandArgument(
      name: 'script',
      isRequired: true,
      description: 'Script name to run.',
      valueType: ValueType::STRING
    ),
    new CommandArgument(
      name: 'args',
      isRequired: false,
      valueType: ValueType::ARRAY
    ),
  ]
)]
class RunCommand extends \Assegai\Cli\Core\AbstractCommand
{

  public function execute(IArgumentHost $context): int
  {
    // TODO: Implement execute() method.
    return Command::SUCCESS;
  }
}