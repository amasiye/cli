<?php

namespace Assegai\Cli\Database;

use Assegai\Cli\Enumerations\DataSourceType;
use Assegai\Cli\Interfaces\IDataSource;

class DataSourceFactory
{
  public static function get(string $driver, string $name): IDataSource
  {
    return new DataSource(
      options: new DataSourceOptions(name: $name, type: DataSourceType::from($driver))
    );
  }
}