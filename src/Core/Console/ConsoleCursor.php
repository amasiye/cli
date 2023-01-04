<?php

namespace Assegai\Cli\Core\Console;

/**
 *
 */
final class ConsoleCursor
{
  /**
   * Moves the cursor to the home position (0, 0)
   *
   * @param bool $return
   * @return string|null Returns a string containing the ESC Code sequence.
   */
  public static function moveHome(bool $return = false): ?string
  {
    $code = "\033[H";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * moves cursor to line #, column #
   *
   * @param int $line The line number to move to.
   * @param int $column The column number to move to.
   * @param bool $return
   * If used and set to true, moveUpBy will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function goto(int $line, int $column, bool $return = false): ?string
  {
    $code = "\033[$line;${column}H";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Moves cursor up # lines
   *
   * @param int $numberOfLines The number of lines to move up by
   * @param bool $return
   * If used and set to true, moveUpBy will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function moveUpBy(int $numberOfLines, bool $return = false): ?string
  {
    $code = "\033[${numberOfLines}A";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   *
   * @param int $numberOfLines
   * @param bool $return
   * If used and set to true, moveUpBy will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function moveDownBy(int $numberOfLines, bool $return = false): ?string
  {
    $code = "\033[${numberOfLines}B";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * @param int $numberOfColumns
   * @param bool $return
   * If used and set to true, moveUpBy will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function moveRightBy(int $numberOfColumns, bool $return = false): ?string
  {
    $code = "\033[${numberOfColumns}C";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * @param int $numberOfColumns
   * @param bool $return
   * If used and set to true, moveUpBy will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function moveLeftBy(int $numberOfColumns, bool $return = false): ?string
  {
    $code = "\033[${numberOfColumns}D";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * @param int $numberOfLines
   * @param bool $return
   * If used and set to true, moveStartNextBy will return a string containing ANSI code instead of outputting it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function moveStartNextBy(int $numberOfLines, bool $return = false): ?string
  {
    $code = "\033[${numberOfLines}E";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Moves cursor to beginning of previous line, # lines up.
   *
   * @param int $numberOfLines The number of lines up.
   * @param bool $return
   * If used and set to true, moveUpBy will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function moveStartPreviousBy(int $numberOfLines, bool $return = false): ?string
  {
    $code = "\033[${numberOfLines}F";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Moves cursor to column #.
   *
   * @param int $column The column number to move the cursor to.
   * @param bool $return
   * If used and set to true, moveUpBy will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function moveToColumn(int $column, bool $return = false): ?string
  {
    $code = "\033[${column}G";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Request cursor position (reports as ESC[#;#R)
   *
   * @param bool $return
   * If used and set to true, moveUpBy will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function getPosition(bool $return = false): ?string
  {
    $code = "\033[6n";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Moves cursor one line up, scrolling if needed
   *
   * @param bool $return
   * If used and set to true, moveUp will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function moveUp(bool $return = false): ?string
  {
    return ConsoleCursor::moveUpBy(numberOfLines: 1);
  }

  /**
   * Moves cursor one line down, scrolling if needed
   *
   * @param bool $return
   * If used and set to true, moveDown will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function moveDown(bool $return = false): ?string
  {
    return ConsoleCursor::moveDownBy(numberOfLines: 1);
  }

  /**
   * Save cursor position (DEC) or (SCO)
   *
   * @param bool $useSCO Specifies whether to use SCO sequences instead of DEC
   * @param bool $return
   * If used and set to true, moveUpBy will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function savePosition(bool $useSCO = true, bool $return = false): ?string
  {
    $code = $useSCO ? "\033[s" : "\033 7";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * Restores the cursor to the last saved position (DEC) or (SCO)
   *
   * @param bool $useSCO Specifies whether to use SCO sequences instead of DEC
   * @param bool $return
   * If used and set to true, moveUpBy will return a string containing ANSI code instead of outputing it.
   *
   * @return null|string Returns a string containing ANSI code or null if `$return` is set to false.
   */
  public static function restorePosition(bool $useSCO = true, bool $return = false): ?string
  {
    $code = $useSCO ? "\033[u" : "\033 8";
    if ($return)
    {
      return $code;
    }

    echo $code;
    return null;
  }

  /**
   * @param bool $isVisible
   * @return void
   */
  public static function setVisibility(bool $isVisible): void
  {
    if ($isVisible)
    {
      system(command: "tput cnorm");
    }
    else
    {
      system(command: "tput civis");
    }
  }
}