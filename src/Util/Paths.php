<?php

namespace Assegai\Cli\Util;

use Exception;

/**
 * Defines static methods for managing some useful paths.
 */
final class Paths
{
  /**
   * Constructs a Paths object.
   */
  private function __construct()
  {
  }

  /**
   * Returns the current working directory.
   *
   * @return string The current working directory.
   */
  public static function getWorkingDirectory(): string
  {
    return trim(shell_exec("pwd"));
  }

  /**
   * Returns the path to the global assegai directory.
   *
   * @return string The path to the global assegai directory.
   * @throws Exception If the global assegai directory cannot be found.
   */
  public static function getAssegaiPath(): string
  {
    $path = match(true) {
      PHP_OS_FAMILY === 'Windows' => exec("where assegai"),
      default => exec("which assegai")
    };

    if (empty($path))
    {
      throw new Exception("Could not find assegai executable.");
    }

    return $path;
  }

  /**
   * Returns the path to the global assegai directory.
   *
   * @return string The path to the global assegai directory.
   */
  public static function getCliBaseDirectory(): string
  {
    # Attempt to get the global assegai path
    return dirname(__DIR__, 2);
  }

  /**
   * Returns the path to the resource directory.
   *
   * @return string The path to the resource directory.
   */
  public static function getResourceDirectory(): string
  {
    return self::getCliBaseDirectory() . '/res';
  }

  /**
   * Returns the path to the assegai.json config file.
   *
   * @return string The path to the assegai.json config file.
   */
  public static function getAssegaiJsonConfigPath(): string
  {
    return self::getWorkingDirectory() . '/assegai.json';
  }

  /**
   * Returns the path to the config directory.
   *
   * @return string The path to the config directory.
   */
  public static function getConfigDirectory(): string
  {
    return self::getWorkingDirectory() . '/config';
  }

  /**
   * Returns the path to the CLI Schematics directory.
   *
   * @return string The path to the CLI Schematics directory.
   */
  public static function getCliSchematicsDirectory(): string
  {
    return self::join(self::getCliBaseDirectory(), 'src/Schematics');
  }

  /**
   * Joins the given paths together.
   *
   * @param string ...$paths The paths to join.
   * @return string The joined path.
   */
  public static function join(string ...$paths): string
  {
    $path = '';

    foreach ($paths as $index => $p)
    {
      if ($index === 0 && str_starts_with($p, '/'))
      {
        $path = ltrim($path, '/');
      }

      if (is_string($p))
      {
        $path .= "$p/";
      }
    }

    if (str_starts_with($path, '/phar:'))
    {
      $path = ltrim($path, '/');
    }

    return rtrim($path, '/');
  }

  /**
   * Transforms the given path into a pascal case string.
   *
   * @param string $path The path to transform.
   * @return string The pascal case string.
   */
  public static function pascalize(string $path): string
  {
    $tokens = explode(DIRECTORY_SEPARATOR, $path);
    $parts = [];

    foreach ($tokens as $token)
    {
      $parts[] = Text::pascalize($token);
    }

    return implode(DIRECTORY_SEPARATOR, $parts);
  }
}