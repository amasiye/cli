<?php

namespace Assegai\Cli\Util;

use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Enumerations\DataSourceType;
use Assegai\Cli\Exceptions\WorkspaceException;
use stdClass;

final class Config
{
  public static function get(?string $path = null): mixed
  {
    $assegaiConfig = Paths::getWorkingDirectory() . '/assegai.json';

    if (!file_exists($assegaiConfig))
    {
      Console::error(obj: "Missing config file!", exit: true);
    }

    $configFileContent = file_get_contents($assegaiConfig);

    if (empty($configFileContent))
    {
      Console::error(obj: "Empty config file!", exit: true);
    }

    if (!str_starts_with($configFileContent, "{") && !str_starts_with($configFileContent, "["))
    {
      Console::error(obj: "Invalid config file", exit: true);
    }

    $config = json_decode($configFileContent);
    $path = explode(separator: '.', string: $path);

    foreach ($path as $token)
    {
      if (!isset($config->$token))
      {
        break;
      }
      $config = $config->$token;
    }

    return $config;
  }

  /**
   * @param string|null $name
   * @return array|object|null
   * @throws WorkspaceException
   */
  public static function getProjectConfig(?string $name = null): array|object|null
  {
    $defaultConfigPath = Paths::join(Paths::getWorkingDirectory(), 'config/default.php');

    if (! file_exists($defaultConfigPath) )
    {
      throw new WorkspaceException("Default config file not found.");
    }

    $config = require($defaultConfigPath);

    $localConfigPath = str_replace('default.php', 'local.php', $defaultConfigPath);

    if (file_exists($localConfigPath))
    {
      $config = require($localConfigPath);
    }

    return empty($name) ? $config : ($config[$name] ?? null);
  }

  /**
   * @param DataSourceType|null $type
   * @param string|null $name
   * @return array|object|null
   * @throws WorkspaceException
   */
  public static function getProjectDatabases(?DataSourceType $type = null, ?string $name = null): array|object|null
  {
    $config = self::getProjectConfig('databases');

    if (!empty($type))
    {
      $config = $config[$type->value] ?? $config;

      if (!empty($name))
      {
        $config = $config[$name] ?? $config;
      }
    }

    return $config;
  }

  /**
   * @return array
   * @throws WorkspaceException
   */
  public static function getAvailableDataSourceTypes(): array
  {
    return array_keys(self::getProjectDatabases());
  }

  /**
   * @param string|DataSourceType $type
   * @return array
   * @throws WorkspaceException
   */
  public static function getAvailableDataSources(string|DataSourceType $type): array
  {
    if (!$type)
    {
      return [];
    }

    if (is_string($type))
    {
      $type = DataSourceType::from($type);
    }

    return array_keys(self::getProjectDatabases(type: $type));
  }

  /**
   * @param string $path
   * @return stdClass|null
   */
  private static function parse(string $path): ?stdClass
  {
    return null;
  }
}