<?php

namespace Assegai\Cli\Util;

use Assegai\Cli\Core\Console\Console;
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

  private static function parse(string $path): ?stdClass
  {
    return null;
  }
}