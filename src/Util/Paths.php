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
    return rtrim($path, '/');
  }

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