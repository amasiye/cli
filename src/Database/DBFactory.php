<?php

namespace Assegai\Cli\Database;

use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Exceptions\FileNotFoundException;
use Assegai\Cli\Util\Paths;
use PDO;
use PDOException;

class DBFactory
{
  private static array $errors = [];
  private static array $options = [
    'dontExit' => false
  ];
  private static array $config = [];

  private static array $connections = [
    'mysql'   => [],
    'mariadb' => [],
    'pgsql'   => [],
    'sqlite'  => [],
    'mongodb' => [],
  ];

  /**
   * @return void
   * @throws FileNotFoundException
   */
  public static function configure(): void
  {
    $configPath = Paths::join(Paths::getWorkingDirectory(), DatabaseSelector::CONFIG_FILE_PATH);
    $localConfigPath = Paths::join(Paths::getWorkingDirectory(), DatabaseSelector::LOCAL_CONFIG_FILE_PATH);

    if (!file_exists($configPath))
    {
      throw new FileNotFoundException(DatabaseSelector::CONFIG_FILE_PATH);
    }

    self::$config = require($configPath);

    if (file_exists($localConfigPath))
    {
      $localConfig = require($localConfigPath);
      if (is_array($localConfig))
      {
        self::$config = array_merge(self::$config, $localConfig);
      }
    }
  }

  public static function errors(): array
  {
    return self::$errors;
  }

  public static function getDatabaseTypes(): array
  {
    $databases = self::$config['databases'] ?? [];

    if ($databaseTypes = array_keys($databases))
    {
      return $databaseTypes;
    }

    return [];
  }

  public static function getSQLConnection(array $config, ?string $dialect = 'mysql'): PDO {
    return match ($dialect) {
      'mariadb'     => DBFactory::getMariaDBConnection(config: $config),
      'pgsql', 'postgresql' => DBFactory::getPostgreSQLConnection(config: $config),
      'sqlite'      => DBFactory::getSQLiteConnection(config: $config),
      default       => DBFactory::getMySQLConnection(config: $config)
    };
  }

  public static function getMySQLConnection(array $config): PDO
  {
    $type = 'mysql';
    $options = array_intersect_key( $config, self::$options);
    $options = array_merge(self::$options, $options);
    extract($config);

    try
    {
      DBFactory::$connections[$type][$name] = new PDO(
        dsn: "mysql:host=$host;port=$port;dbname=$name",
        username: $user,
        password: $password
      );
    }
    catch (PDOException $e)
    {
      self::$errors[$e->getCode()] = $e->getMessage();
      if ($options['dontExit'] === false)
      {
        exit($e->getMessage());
      }

      DBFactory::$connections[$type][$name] = match($e->getCode()) {
        default => new PDO(
          dsn: "mysql:host=$host;port=$port",
          username: $user,
          password: $password
        )
      };
    }

    if (!isset(DBFactory::$connections[$type][$name]))
    {
      Console::error(obj: 'Connection error.', exit: true);
    }

    $connection = DBFactory::$connections[$type][$name];

    if (is_null($connection))
    {
      Console::error(obj: 'Connection error. Make sure your database server is running.', exit: true);
    }

    return $connection;
  }

  public static function getMariaDBConnection(array $config): PDO
  {
    $type = 'mariadb';
    $options = array_intersect_key( $config, self::$options);
    $options = array_merge(self::$options, $options);
    extract($config);

    try
    {
      DBFactory::$connections[$type][$name] = new PDO(
        dsn: "mysql:host=$host;port=$port;dbname=$name",
        username: $user,
        password: $password
      );
    }
    catch (PDOException $e)
    {
      self::$errors[$e->getCode()] = $e->getMessage();
      if ($options['dontExit'] === false)
      {
        exit($e->getMessage());
      }
    }

    if (!isset(DBFactory::$connections[$type][$name]))
    {
      Console::error(obj: 'Connection error.', exit: true);
    }

    $connection = DBFactory::$connections[$type][$name];

    if (is_null($connection))
    {
      Console::error(obj: 'Connection error. Make sure your database server is running.', exit: true);
    }

    return $connection;
  }

  public static function getPostgreSQLConnection(array $config): PDO
  {
    $type = 'pgsql';
    $options = array_intersect_key( $config, self::$options);
    $options = array_merge(self::$options, $options);
    extract($config);

    try
    {
      DBFactory::$connections[$type][$name] = new PDO(
        dsn: "pgsql:host=$host;port=$port;dbname=$name",
        username: $user,
        password: $password
      );
    }
    catch (PDOException $e)
    {
      self::$errors[$e->getCode()] = $e->getMessage();
      if ($options['dontExit'] === false)
      {
        exit($e->getMessage());
      }
    }

    if (!isset(DBFactory::$connections[$type][$name]))
    {
      Console::error(obj: 'Connection error.', exit: true);
    }

    $connection = DBFactory::$connections[$type][$name];

    if (is_null($connection))
    {
      Console::error(obj: 'Connection error. Make sure your database server is running.', exit: true);
    }

    return $connection;
  }

  public static function getSQLiteConnection(array $config): PDO
  {
    $type = 'sqlite';
    $options = array_intersect_key( $config, self::$options);
    $options = array_merge(self::$options, $options);
    extract($config);

    try
    {
      DBFactory::$connections[$type][$name] = new PDO( dsn: "sqlite:$path" );
    }
    catch (PDOException $e)
    {
      self::$errors[$e->getCode()] = $e->getMessage();
      if ($options['dontExit'] === false)
      {
        exit($e->getMessage());
      }
    }

    if (!isset(DBFactory::$connections[$type][$name]))
    {
      Console::error(obj: 'Connection error.', exit: true);
    }

    $connection = DBFactory::$connections[$type][$name];

    if (is_null($connection))
    {
      Console::error(obj: 'Connection error. Make sure your database server is running.', exit: true);
    }

    return $connection;
  }

  public static function getMongoDbConnection(array $config): PDO
  {
    $type = 'mongodb';
    $options = array_intersect_key( $config, self::$options);
    $options = array_merge(self::$options, $options);
    extract($config);

    if (!isset($name))
    {
      Console::error("Missing name", exit: true);
    }

    try
    {
      // TODO: Implement MongoDB connection
    }
    catch (PDOException $e)
    {
      self::$errors[$e->getCode()] = $e->getMessage();
      if ($options['dontExit'] === false)
      {
        exit($e->getMessage());
      }
    }

    if (!isset(DBFactory::$connections[$type][$name]))
    {
      Console::error(obj: 'Connection error.', exit: true);
    }

    $connection = DBFactory::$connections[$type][$name];

    if (is_null($connection))
    {
      Console::error(obj: 'Connection error. Make sure your database server is running.', exit: true);
    }

    return $connection;
  }
}