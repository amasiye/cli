<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Action;
use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Attributes\ValidateWorkspace;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Core\CommandOption;
use Assegai\Cli\Core\Menus\Menu;
use Assegai\Cli\Core\Menus\MenuItem;
use Assegai\Cli\Database\DataSourceFactory;
use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Enumerations\ValueType;
use Assegai\Cli\Exceptions\WorkspaceException;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IDataSource;
use Assegai\Cli\Interfaces\IEntityManager;
use Assegai\Cli\Interfaces\IExecutionContext;
use Assegai\Cli\Util\Config;
use ReflectionException;

/**
 *
 */
#[Command(
  name: 'schema',
  description: 'Manages database schema.',
  options: [
    new CommandOption(
      name: 'data-source',
      alias: 'd',
      type: ValueRequirementType::REQUIRED,
      description: 'Path to the file where your Repository instance is defined.',
      valueType: ValueType::STRING
    )
  ],
  arguments: [
    new CommandArgument(
      name: 'action',
      isRequired: true,
      description: 'The action to perform on the schema',
      valueType: ValueType::STRING
    )
  ]
)]
class SchemaCommand extends AbstractCommand
{
  protected ?IDataSource $dataSource = null;
  private ?IEntityManager $entityManager = null;

  /**
   * @param IArgumentHost $context
   * @return int
   * @throws ReflectionException
   */
  #[ValidateWorkspace]
  public function execute(IArgumentHost $context): int
  {
    return $this->handle(action: $this->args->action, context: $context);
  }

  /**
   * @param IExecutionContext $context
   * @return int
   * @throws WorkspaceException
   */
  #[Action(
    description: 'Synchronizes your entities with their respective database schema. ' .
      'It runs schema update queries on all connections you have.'
  )]
  public function sync(IExecutionContext $context): int
  {
    // TODO: Implement sync() method.
    $availableTypes = Config::getAvailableDataSourceTypes();
    $typeMenus = new Menu(
      title: '',
      items: []
    );
    foreach ($availableTypes as $type)
    {
      $typeMenus->add(new MenuItem($type));
    }
    $databaseMenu = new Menu(title: '', items: []);

    $dataSource = $this->options->dataSource ?? '';
    $tokens = explode(':', $dataSource);

    $driver = $tokens[0] ?? null;
    if (!$driver)
    {
      $driver = $typeMenus->prompt(message: "Data source type", useKeypad: true)->value();
    }

    $availableDatabases = Config::getAvailableDataSources(type: $driver);
    foreach ($availableDatabases as $database)
    {
      $databaseMenu->add(new MenuItem($database));
    }
    $schema = $tokens[1] ?? null;
    if (!$schema)
    {
      $schema = $databaseMenu->prompt(message: 'Data source name', useKeypad: true)->value();
    }

    $dataSource = DataSourceFactory::get(driver: $driver, name: $schema);

    $dataSource->synchronize();
    # For each entity
    $entities = $this->getScopeEntities();

    foreach ($entities as $entity)
    {
      if ($this->canSynchronize($entity))
      {
        $this->entityManager?->sync($entity);
      }
    }

    return Command::SUCCESS;
  }

  /**
   * @param IExecutionContext $context
   * @return int
   */
  #[Action]
  public function log(IExecutionContext $context): int
  {
    // TODO: Implement log() method.
    return Command::SUCCESS;
  }

  /**
   * @param IExecutionContext $context
   * @return int
   */
  #[Action]
  public function drop(IExecutionContext $context): int
  {
    // TODO: Implement drop() method.
    return Command::SUCCESS;
  }

  /**
   * @return array
   */
  private function getScopeEntities(): array
  {
    return [];
  }

  /**
   * @param mixed $entity
   * @return bool
   */
  private function canSynchronize(mixed $entity): bool
  {
    return true;
  }
}