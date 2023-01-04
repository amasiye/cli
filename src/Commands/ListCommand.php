<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Core\CommandOption;
use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Enumerations\ValueType;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IExecutionContext;
use Assegai\Cli\Util\Paths;

#[Command(
  name: 'list',
  usage: 'list',
  shortName: 'l',
  description: 'Prints a list of available items in a collection [DEFAULT: Prints available commands].',
  options: [
    new CommandOption(
      name: 'verbose',
      alias: 'v',
      type: ValueRequirementType::NOT_ALLOWED,
      description: 'Output more information.',
      valueType: ValueType::BOOLEAN,
      defaultValue: false
    ),
  ],
  arguments: [
    new CommandArgument(
      name: 'collection',
      description: 'List the elements of the given collection. [DEFAULT=commands]',
      valueType: ValueType::STRING,
      defaultValue: 'commands'
    ),
  ]
)]
class ListCommand extends AbstractCommand
{
  public function execute(IArgumentHost|IExecutionContext $context): int
  {
    $collection = $this->args->collection ?? 'commands';

    switch ($collection)
    {
      case 'databases':
        $this->listDatabases(context: $context);
        break;

      default: $this->listCommands(context: $context);
    }

    return Command::SUCCESS;
  }

  private function listCommands(IExecutionContext $context): void
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
  }

  private function listDatabases(IExecutionContext $context): void
  {
    $CONFIG_PATH = Paths::getConfigDirectory();
    $configFilename = $CONFIG_PATH . '/default.php';
    $localConfigFilename = $CONFIG_PATH . '/local.php';
    $localConfig = [];

    $config = require($configFilename);

    if (file_exists($localConfigFilename))
    {
      $localConfig = require($localConfigFilename);
    }

    $config = array_merge($config, $localConfig);
    $databases = $config['databases'];

    printf("%sAvailable database:%s" . PHP_EOL, Color::YELLOW, Color::RESET);

    if ($databases)
    {
      echo PHP_EOL;
      foreach ($databases as $type => $dbList)
      {
        printf("%s%s%s:" . PHP_EOL, Color::BLUE, $type, Color::RESET);
        if (isset($this->options->verbose))
        {
          foreach ($dbList as $name => $item)
          {
            $db = (object)$item;
            if (isset($db->host) && isset($db->port))
            {
              printf("  %s@%s:%s" . PHP_EOL, $name, $db->host, $db->port);
            }
            else
            {
              printf("  %s" . PHP_EOL, $name);
            }
          }
        }
        else
        {
          $names = array_keys($dbList);
          foreach ($names as $name)
          {
            printf("  %s" . PHP_EOL, $name);
          }
        }
      }
    }
    else
    {
      echo "None" . PHP_EOL;
    }
  }
}