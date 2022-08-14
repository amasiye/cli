<?php

namespace Assegai\Cli\Util;

use Assegai\Cli\Exceptions\FileException;

final class Files
{
  private function __construct()
  {}

  /**
   * @param string $file1
   * @param string $file2
   * @return bool
   */
  private static function filesAreEqual(string $file1, string $file2): bool
  {
    return md5_file($file1) === md5_file($file2);
  }

  /**
   * @param string $file1
   * @param string $file2
   * @return bool
   */
  private static function filesAreNotEqual(string $file1, string $file2): bool
  {
    return !self::filesAreEqual($file1, $file2);
  }

  /**
   * @param string $from
   * @param string $to
   * @return void
   * @throws FileException
   */
  public static function renameFile(string $from, string $to): void
  {
    if (strcmp($from, $to) !== 0)
    {
      $command = "mv -f $from $to";

      if (false === @passthru($command))
      {
        throw new FileException("Could not rename $from to " . basename($to));
      }
    }
  }
}