<?php

namespace Assegai\Cli\Database;

use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Core\Menus\Menu;
use Assegai\Cli\Core\Menus\MenuItem;
use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Util\Paths;
use PDO;
use PDOException;

/**
 *
 */
final class DatabaseSelector
{
  /**
   *
   */
  const CONFIG_FILE_PATH        = 'config/default.php';
  /**
   *
   */
  const LOCAL_CONFIG_FILE_PATH  = 'config/local.php';
  /**
   *
   */
  const PROD_CONFIG_FILE_PATH   = 'config/production.php';

  /**
   * @var PDO|null
   */
  private ?PDO $connection = null;

  /**
   * @var array
   */
  private array $config = [];
  /**
   * @var Menu|null
   */
  private ?Menu $databaseTypesMenu;
  /**
   * @var Menu|null
   */
  private ?Menu $availableDatabasesMenu;

  /**
   * @param string|null $databaseType
   * @param string|null $databaseName
   * @param bool $promptToCreate
   */
  public function __construct(
    private ?string $databaseType = null,
    private ?string $databaseName = null,
    private readonly bool $promptToCreate = false
  )
  {
    $this->databaseTypesMenu = new Menu(title: 'Database Types:');
    $this->availableDatabasesMenu = new Menu(title: 'Available Databases:');
  }

  /**
   * @return string|null
   */
  public function getDatabaseType(): ?string { return $this->databaseType; }

  /**
   * @return string|null
   */
  public function getDatabaseName(): ?string { return $this->databaseName; }

  /**
   * @return array
   */
  public function config(): array { return $this->config; }

  /**
   * @return PDO|null
   */
  public function connection(): ?PDO { return $this->connection; }

  /**
   * @return void
   */
  public function run(): void
  {
    $workingDirectory = Paths::getWorkingDirectory();
    $availableDatabases = [];

    if (!file_exists(self::CONFIG_FILE_PATH))
    {
      Console::error("Missing file: " . self::CONFIG_FILE_PATH, exit: true);
    }

    $this->config = require(self::CONFIG_FILE_PATH);

    if (file_exists("$workingDirectory/" . self::LOCAL_CONFIG_FILE_PATH))
    {
      $localConfig = require("$workingDirectory/" . self::LOCAL_CONFIG_FILE_PATH);
      if (is_array($localConfig))
      {
        $this->config = array_merge($this->config, $localConfig);
      }
    }

    if (!isset($this->config['databases']))
    {
      Console::error("Invalid config: 'databases' is not defined.", exit: true);
    }

    # 1. Select Database Type
    if (is_null($this->getDatabaseType()))
    {
      $availableDatabases = array_keys($this->config['databases']);

      foreach ($availableDatabases as $db)
      {
        $this->databaseTypesMenu->add(new MenuItem(value: $db));
      }
      $this->databaseTypesMenu->add(new MenuItem(value: 'quit'));
      $choice = $this->databaseTypesMenu->prompt(message: 'Database type', useKeypad: true);

      if (is_null($choice))
      {
        Console::error('Invalid choice', exit: true);
      }

      if ($this->isQuitRequest(input: $choice->value()))
      {
        exit(0);
      }
      $this->databaseType = $choice->value();
    }

    # 2. Select Database
    if (is_null($this->getDatabaseName()))
    {
      $availableDatabases = array_keys($this->config['databases'][$this->getDatabaseType()]);

      foreach ($availableDatabases as $db)
      {
        $this->availableDatabasesMenu->add(new MenuItem(value: $db));
      }
      $this->availableDatabasesMenu->add(new MenuItem(value: 'quit'));
      $choice = $this->availableDatabasesMenu->prompt(message: 'Database name', useKeypad: true);
      echo PHP_EOL;

      if (is_null($choice))
      {
        Console::error('Invalid choice');
      }

      if ($this->isQuitRequest(input: $choice->value()))
      {
        exit(0);
      }

      if (!isset($this->config['databases'][$this->getDatabaseType()][$choice->value()]))
      {
        Console::error('Invalid choice');
      }

      $this->databaseName = $choice->value();
    }

    # 3. Establish Database connection
    if (!isset($this->config['databases'][$this->getDatabaseType()]))
    {
      Console::error('Unknown database type: ' . $this->getDatabaseType(), exit: true);
    }

    if (!isset($this->config['databases'][$this->getDatabaseType()][$this->getDatabaseName()]))
    {
      Console::error('Unknown database name: ' . $this->getDatabaseName(), exit: true);
    }

    $this->config = $this->config['databases'][$this->getDatabaseType()][$this->getDatabaseName()];
    $this->config['dontExit'] = true;
    $this->config['name'] = $this->getDatabaseName();
    $this->connection = DBFactory::getSQLConnection(config: $this->config, dialect: $this->getDatabaseType());

    if (!empty(DBFactory::errors()))
    {
      # Search for database doesn't exist error(1049)
      foreach (DBFactory::errors() as $index => $error)
      {
        if ($index === 1049)
        {
          printf("Unknown database '%s%s%s'.\n\n", Color::YELLOW, $this->getDatabaseName(), Color::RESET);
          if ($this->promptToCreate)
          {
            $answer = $this->readLine(message: 'Would you like to create it? ', suffix: '[Y/n]', defaultValue: 'y');
            $answer = match(strtolower($answer)) {
              'y',
              'yes',
              'yep',
              'yeah'  => 'yes',
              default => 'no'
            };

            if ($answer === 'no')
            {
              Console::warn('Database not defined. Terminating program.', exit: true);
            }
            else
            {
              try
              {
                $statement = $this->connection()->query(sprintf("CREATE DATABASE IF NOT EXISTS `%s` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci", $this->getDatabaseName()));

                if ($statement === false)
                {
                  Console::error(implode("\n", $this->connection()->errorInfo()), exit: true);
                }

                Console::logCreate($this->getDatabaseName() . ' database');
              }
              catch(PDOException $e)
              {
                Console::error($e->getMessage(), exit: true);
              }
            }
            break;
          }
        }
      }
    }

    unset($this->config['dontExit']);
  }

  /**
   * @param string $message
   * @param string $suffix
   * @param string|null $defaultValue
   * @return string
   */
  private function readLine(
    string $message = '',
    string $suffix = '',
    ?string $defaultValue = null
  ): string
  {
    printf("%s: %s " . Color::BLUE, $message, $suffix);
    $line = trim(fgets(STDIN));
    echo Color::RESET;
    if (empty($line))
    {
      $line = $defaultValue;
    }
    return $line;
  }

  /**
   * @param string $input
   * @return bool
   */
  private function isQuitRequest(string $input): bool
  {
    return in_array(strtolower($input), ['x', 'quit', 'exit', 'kill', 'stop']);
  }
}