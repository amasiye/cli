<?php

namespace Assegai\Cli\Util;

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

  public static function getAssegaiPath(): string
  {
    return trim(shell_exec('echo $ASSEGAI_PATH'));
  }

  public static function getBaseDirectory(): string
  {
    return dirname(__DIR__, 2);
  }

  public static function getResourceDirectory(): string
  {
    return self::getBaseDirectory() . '/res';
  }
}