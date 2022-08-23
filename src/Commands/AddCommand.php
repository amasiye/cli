<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Action;
use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Core\CommandOption;
use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Core\WorkspaceManager;
use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Enumerations\ValueType;
use Assegai\Cli\Exceptions\WorkspaceException;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IExecutionContext;
use Assegai\Cli\Util\Paths;

/**
 *
 */
#[Command(
  name: 'add',
  description: 'Imports a library that has been packaged as an assegai library, running its install schematic.',
  options: [
    new CommandOption(name: 'dry-run', alias: 'd', description: 'Report actions that would be performed without writing out results.'),
    new CommandOption(name: 'project', alias: 'p', type: ValueRequirementType::REQUIRED, description: 'Project in which to generate files.', valueType: ValueType::STRING)
  ],
  arguments: [
    new CommandArgument(
      name: 'library',
      isRequired: true,
      description: 'The name of the library to import.',
      valueType: ValueType::STRING
    )
  ]
)]
class AddCommand extends AbstractCommand
{
  private string $composer = 'composer';
  private string $command = '';

  /**
   * @param IArgumentHost $context
   * @return int
   * @throws WorkspaceException
   * @throws \ReflectionException
   */
  public function execute(IArgumentHost $context): int
  {
    Console::warn("Not implemented");

    $package = "assegaiphp/" . $this->args->library;

    if (preg_match('/\w+\/\w+/', $this->args->library))
    {
      $package = $this->args->library;
    }

    $this->command = "require $package";

    if (WorkspaceManager::hasLocalComposer())
    {
      $this->composer = Paths::join(Paths::getWorkingDirectory(), 'composer.phar');
    }
    else if (WorkspaceManager::hasGlobalComposer())
    {
      $this->composer = 'composer';
    }
    else
    {
      throw new WorkspaceException("Could not find composer executable.");
    }

    if ($this->getAction($this->args->library))
    {
      return $this->handle($this->args->library, $context);
    }

    if (false === system("$this->composer $this->command", $errorCode))
    {
      throw new WorkspaceException("Failed to execute $this->command. Error Code - $errorCode");
    }

    return Command::SUCCESS;
  }

  /**
   * @param IExecutionContext $context
   * @return int
   * @throws WorkspaceException
   */
  #[Action]
  public function orm(IExecutionContext $context): int
  {
    Console::info('Adding assegaiphp/orm...');
    if (false === system("$this->composer $this->command", $errorCode))
    {
      throw new WorkspaceException("Failed to execute $this->command. Error Code - $errorCode");
    }
    return Command::SUCCESS;
  }
}