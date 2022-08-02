<?php

namespace Assegai\Cli\Util;

/**
 * Defines static methods for managing some useful paths.
 */
final class Paths
{
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
  public static function getBaseDirectory(): string
  {
    return dirname(__DIR__, 2);
  }

  /**
   * @return string
   */
  public static function getResourceDirectory(): string
  {
    return self::getBaseDirectory() . '/res';
  }

  /**
   * @return string
   */
  public static function getAssegaiConfigPath(): string
  {
    return self::getWorkingDirectory() . '/assegai.json';
  }
}