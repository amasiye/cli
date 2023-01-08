<?php

namespace Assegai\Cli\Database;

use Assegai\Cli\Enumerations\DataSourceType;
use Assegai\Cli\Exceptions\WorkspaceException;
use Assegai\Cli\Util\Config;

/**
 *
 */
class DataSourceOptions
{
  /**
   * @var DataSourceConnectionOptions
   */
  public readonly DataSourceConnectionOptions $connectionOptions;

  /**
   * @param string $name
   * @param DataSourceType $type
   * @param DataSourceConnectionOptions|null $connectionOptions
   * @throws WorkspaceException
   */
  public function __construct(
    public readonly string $name,
    public readonly DataSourceType $type = DataSourceType::MYSQL,
    ?DataSourceConnectionOptions $connectionOptions = null
  )
  {
    $config = Config::getProjectDatabases(type: $this->type, name: $this->name);
    $this->connectionOptions = $connectionOptions ?? new DataSourceConnectionOptions(
      name: $this->name,
      host: $config['host'] ?? 'localhost',
      user: $config['user'] ?? 'root',
      password: $config['password'] ?? '',
      port: $config['port'] ?? 3306
    );
  }
}