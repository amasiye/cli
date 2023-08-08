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