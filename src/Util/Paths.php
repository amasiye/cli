<?php

namespace Assegai\Cli\Util;

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
   * @return string
   */
  public static function getWorkingDirectory(): string
  {
    return trim(shell_exec("pwd"));
  }

  /**
   * @return string
   */
  public static function getAssegaiPath(): string
  {
    return trim(shell_exec('echo $ASSEGAI_PATH'));
  }

  /**
   * @return string
   */
  public static function getCliBaseDirectory(): string
  {
    # Attempt to get the global assegai path
    return dirname(__DIR__, 2);
  }

  /**
   * @return string
   */
  public static function getResourceDirectory(): string
  {
    return self::getCliBaseDirectory() . '/res';
  }

  /**
   * @return string
   */
  public static function getAssegaiJsonConfigPath(): string
  {
    return self::getWorkingDirectory() . '/assegai.json';
  }

  /**
   * @return string
   */
  public static function getConfigDirectory(): string
  {
    return self::getWorkingDirectory() . '/config';
  }

  /**
   * @return string
   */
  public static function getCliSchematicsDirectory(): string
  {
    return self::join(self::getCliBaseDirectory(), 'src/Schematics');
  }

  /**
   * @param ...$paths
   * @return string
   */
  public static function join(...$paths): string
  {
    $path = '';

    foreach ($paths as $p)
    {
      if (is_string($p))
      {
        $path .= "/$p";
      }
    }

    $path = preg_replace('/\/+/', '/', $path);

    if (str_contains($path, 'assegai.phar') && str_starts_with($path, '/phar:'))
    {
      $path = ltrim($path, '/');

      if (!preg_match('/phar:\/\/\//', $path))
      {
        $path = match(true) {
          str_contains('phar:/', 'phar:///') => str_replace('phar:/', 'phar:///', $path),
          str_contains('phar://', $path) => str_replace('phar://', 'phar:///', $path),
          default => str_replace('phar:', 'phar://', $path)
        };
      }
    }

    return rtrim($path, '/');
  }

  /**
   * @param string $path
   * @return string
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