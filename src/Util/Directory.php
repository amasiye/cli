<?php

namespace Assegai\Cli\Util;

use Assegai\Cli\Core\Console\Console;
use RuntimeException;

/**
 * This class provides utilities for working with directories.
 */
final class Directory
{
  private function __construct()
  {}

  /**
   * Creates a directory.
   *
   * @param string $path The path to the directory to create.
   * @param int $permissions The permissions to set on the directory. Defaults to 0777.
   * @param bool $recursive Whether to create the directory recursively. Defaults to true.
   * @param bool $verbose Whether to log the creation of the directory.
   * @return bool Returns true if the directory was created, false otherwise.
   */
  public static function create(
    string $path,
    int $permissions = 0777,
    bool $recursive = true,
    bool $verbose = false
  ): bool
  {
    if (self::exists($path))
    {
      if ($verbose)
      {
        Console::warn("Directory already exists, $path");
      }

      return false;
    }

    if (false === mkdir($path, $permissions, $recursive))
    {
      if ($verbose)
      {
        Console::error("Failed to create directory, $path");
      }

      return false;
    }

    if ($verbose)
    {
      Console::logFileCreate($path);
    }

    return true;
  }

  /**
   * Creates a temporary directory.
   *
   * @param string $suffix The suffix to append to the temporary directory. Defaults to 'assegai'.
   * @param bool $verbose Whether to log the creation of the temporary directory.
   * @return string Returns the path to the temporary directory.
   */
  public static function createTemporary(
    string $suffix = 'assegai',
    ?string $name = null,
    bool $verbose = false,
  ): string
  {
    $directoryName = $name ?: uniqid($suffix);

    $temporaryDirectory = get_temp_dir();
    if (! is_dir($temporaryDirectory))
    {
      if (false === mkdir($temporaryDirectory, 0777, true))
      {
        throw new RuntimeException("Failed to create directory, $temporaryDirectory");
      }
    }

    $path = Paths::join($temporaryDirectory, $directoryName);

    if (false === self::create($path, $verbose))
    {
      throw new RuntimeException("Failed to create temporary directory, $path");
    }

    return  $path;
  }

  /**
   * Checks if the given path exists and is a directory.
   *
   * @param string $path The path to check.
   * @return bool Returns true if the path exists and is a directory, false otherwise.
   */
  public static function exists(string $path): bool
  {
    return is_dir($path);
  }

  /**
   * Checks if the given path does not exist and is not a directory.
   *
   * @param string $path The path to check.
   * @return bool Returns true if the path does not exist and is not a directory, false otherwise.
   */
  public static function doesNotExist(string $path): bool
  {
    return !self::exists($path);
  }

  /**
   * Deletes a directory.
   *
   * @param string $path
   * @return bool
   */
  public static function delete(string $path): bool
  {
    return delete_directory($path);
  }

  /**
   * Copies a directory.
   *
   * @param string $source The source directory to copy.
   * @param string $target The target directory to copy to.
   * @return void
   */
  public static function copy(string $source, string $target): void
  {
    copy_directory($source, $target);
  }
}