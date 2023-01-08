<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Core\CommandOption;
use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Enumerations\ValueType;
use Assegai\Cli\Exceptions\FileNotFoundException;
use Assegai\Cli\Exceptions\InvalidSchemaException;
use Assegai\Cli\Exceptions\WorkspaceException;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IExecutionContext;

#[Command(
  name: 'new',
  shortName: 'n',
  description: 'Generate an Assegai application.',
  options: [
    new CommandOption(name: 'directory', type: ValueRequirementType::REQUIRED, description: 'Specify the destination directory.', valueType: ValueType::STRING, defaultValue: null),
    new CommandOption(name: 'dry-run', alias: 'd', type: ValueRequirementType::NOT_ALLOWED, description: 'Report actions that would be performed without writing out results. (default: false)', valueType: ValueType::BOOLEAN, defaultValue: false),
    new CommandOption(name: 'skip-git', alias: 'g', type: ValueRequirementType::NOT_ALLOWED, description: 'Skip git repository initialization. (default: false)', valueType: ValueType::BOOLEAN, defaultValue: false),
    new CommandOption(name: 'skip-install', alias: 's', type: ValueRequirementType::NOT_ALLOWED, description: 'Skip package installation. (default: false)', valueType: ValueType::BOOLEAN, defaultValue: false),
  ],
  arguments: [
    new CommandArgument(name: 'name', description: 'The name of the new application/project.', valueType: ValueType::STRING)
  ]
)]
class NewCommand extends AbstractCommand
{
  /**
   * @param IArgumentHost|IExecutionContext $context
   * @return int
   * @throws FileNotFoundException
   * @throws WorkspaceException
   * @throws InvalidSchemaException
   */
  public function execute(IArgumentHost|IExecutionContext $context): int
  {
    $this->workspaceManager->init(projectName: $this->args->name ?? '', args: $this->args);
    $this->workspaceManager->install();

    return Command::SUCCESS;
  }
}