<?php

namespace Assegai\Cli\Core\Console;

final class ConsoleEraser
{
  /**
   * Erase in display (same as ESC[0J)
   */
  public static function inDisplay(bool $return = false): ?string
  {
    $code = "\033[J";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Erase from cursor until end of screen
   */
  public static function toEndOfScreen(bool $return = false): ?string
  {
    $code = "\033[0J";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Erase from cursor to beginning of screen
   */
  public static function toBeginningOfScreen(bool $return = false): ?string
  {
    $code = "\033[1J";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Erase entire screen
   */
  public static function entireScreen(bool $return = false): ?string
  {
    $code = "\033[2J";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Erase saved lines
   */
  public static function savedLines(bool $return = false): ?string
  {
    $code = "\033[3J";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Erase in line (same as ESC[0K)
   */
  public static function inLine(bool $return = false): ?string
  {
    $code = "\033[K";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Erase from cursor to end of line
   */
  public static function toEndOfLine(bool $return = false): ?string
  {
    $code = "\033[0K";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Erase start of line to the cursor
   */
  public static function toStartOfLine(bool $return = false): ?string
  {
    $code = "\033[0K";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Erase the entire line
   */
  public static function entireLine(bool $return = false): ?string
  {
    $code = "\033[0K";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }
}