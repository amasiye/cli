<?php

/**
 * Checks if the given filename is a phar file.
 *
 * @param string $filename The filename to check.
 * @return bool Returns true if the filename is a phar file.
 */
function is_phar(string $filename): bool
{
  try
  {
    $phar = new Phar($filename);
  }
  catch (Exception $e)
  {
    return false;
  }

  return true;
}

/**
 * Checks if the given path is a phar path. This is a path that starts with 'phar://'.
 *
 * @param string $path The path to check.
 * @return bool Returns true if the path is a phar path.
 */
function is_phar_path(string $path): bool
{
  return str_starts_with($path, 'phar://');
}

/* === File System Functions === */
/**
 * Returns the path to the temporary directory.
 *
 * @return string The path to the temporary directory.
 */
function get_temp_dir(): string
{
  return '~/.assegai/tmp';
}

/**
 * Copies the given directory to the given target.
 *
 * @param string $source The source directory to copy.
 * @param string $target The target directory to copy to.
 * @return void
 * @throws RuntimeException Thrown if the directory could not be copied.
 */
function copy_directory(string $source, string $target): void
{
  if (! is_dir($target) )
  {
    if (false === mkdir($target))
    {
      throw new RuntimeException("Failed to create directory, $target");
    }
  }

  $files = scandir($source);

  foreach ($files as $file)
  {
    if ($file === '.' || $file === '..')
    {
      continue;
    }

    $sourcePath = "$source/$file";
    $targetPath = "$target/$file";

    if (is_dir($sourcePath))
    {
      copy_directory($sourcePath, $targetPath);
    }
    else
    {
      copy($sourcePath, $targetPath);
    }
  }
}

/**
 * Deletes the given directory.
 *
 * @param string $directoryPath
 * @return bool
 */
function delete_directory(string $directoryPath): bool
{
  if (! is_dir($directoryPath) )
  {
    return false;
  }

  $files = scandir($directoryPath);

  foreach ($files as $file)
  {
    if ($file === '.' || $file === '..')
    {
      continue;
    }

    if (is_dir("$directoryPath/$file"))
    {
      delete_directory("$directoryPath/$file");
    }
    else
    {
      unlink("$directoryPath/$file");
    }
  }

  return rmdir($directoryPath);
}