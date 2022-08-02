<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Enumerations\FeatureType;
use Assegai\Cli\Enumerations\ValueType;
use Assegai\Cli\Interfaces\IArgumentHost;

#[Command(
  name: 'setup',
  description: 'Runs setup and installation scripts for the given assegai feature.',
  arguments: [
    new CommandArgument(name: 'type', description: 'The type of feature to be setup.', valueType: ValueType::ENUM, defaultValue: FeatureType::DATABASE, enum: FeatureType::class),
    new CommandArgument(name: 'feature', description: 'The name of the feature to be setup.', valueType: ValueType::STRING, defaultValue: null),
  ]
)]
class SetupCommand extends AbstractCommand
{

  public function execute(IArgumentHost $context): int
  {
    return Command::SUCCESS;
  }
}