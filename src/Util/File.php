<?php

namespace Assegai\Cli\Util;

use Assegai\Cli\Exceptions\FileException;

/**
 * Utility class for working with files.
 */
final class File
{
  /**
   * The constructor.
   */
  private function __construct()
  {}

  /**
   * Checks if the given files are equal.
   *
   * @param string $file1 The first file to compare.
   * @param string $file2 The second file to compare.
   * @return bool Returns true if the files are equal, false otherwise.
   */
  private static function areEqual(string $file1, string $file2): bool
  {
    return md5_file($file1) === md5_file($file2);
  }

  /**
   * Checks if the given files are not equal.
   *
   * @param string $file1 The first file to compare.
   * @param string $file2 The second file to compare.
   * @return bool Returns true if the files are not equal, false otherwise.
   */
  private static function areNotEqual(string $file1, string $file2): bool
  {
    return !self::areEqual($file1, $file2);
  }

  /**
   * Renames a file.
   *
   * @param string $from The file to rename.
   * @param string $to The new name of the file.
   * @return void
   * @throws FileException
   */
  public static function rename(string $from, string $to): void
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