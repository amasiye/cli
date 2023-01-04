<?php

namespace Assegai\Cli\Core\Console;

use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Util\Number;
use \Throwable;

/**
 *
 */
final class Console
{
  /**
   *
   */
  const FILE_CREATE = 'CREATE';
  /**
   *
   */
  const FILE_RENAME = 'RENAME';
  /**
   *
   */
  const FILE_UPDATE = 'UPDATE';
  /**
   *
   */
  const FILE_DELETE = 'DELETE';

  /**
   * @param mixed $assertion
   * @param string|Throwable|null $description
   * @return bool
   */
  public static function assert(mixed $assertion, null|string|Throwable $description = null): bool
  {
    // TODO: Implement assert()
    return assert(assertion: $assertion, description: $description);
  }

  /**
   * @param string $message
   * @param string $color
   * @return void
   */
  public static function print(string $message, string $color = Color::RESET): void
  {
    printf("%s%s%s" . PHP_EOL, $color, $message, Color::RESET);
  }

  /**
   * @param string $message
   * @param bool $exit
   * @return void
   */
  public static function log(string $message, bool $exit = false): void
  {
    if ($exit)
    {
      exit($message . PHP_EOL);
    }

    echo $message . PHP_EOL;
  }

  /**
   * @param mixed $obj
   * @param bool $exit
   * @return void
   */
  public static function info(mixed $obj, bool $exit = false): void
  {
    self::log(message: sprintf("%sInfo: %s%s", Color::BLUE, self::objectToString($obj), Color::RESET), exit: $exit);
  }

  /**
   * @param mixed $obj
   * @param bool $exit
   * @return void
   */
  public static function warn(mixed $obj, bool $exit = false): void
  {
    self::log(message: sprintf("%sWarning: %s%s", Color::YELLOW, self::objectToString($obj), Color::RESET), exit: $exit);
  }

  /**
   * @param mixed $obj
   * @param bool $exit
   * @return void
   */
  public static function error(mixed $obj, bool $exit = false): void
  {
    self::log(message: sprintf("%sError: %s%s", Color::RED, self::objectToString($obj), Color::RESET), exit: $exit);
  }

  /**
   * @param string $message
   * @param string|null $defaultValue
   * @param int|null $attempts
   * @return string
   */
  public static function prompt(
    string $message = 'Enter choice',
    ?string $defaultValue = null,
    ?int $attempts = null
  ): string
  {
    $defaultHint = '';
    if (!empty($defaultValue))
    {
      $defaultHint = Color::DARK_WHITE . "($defaultValue) " . Color::RESET;
    }

    $isValid = false;
    $attemptsLeft = $attempts;

    do
    {
      printf("%s?%s %s: %s%s", Color::GREEN, Color::RESET, $message, $defaultHint, Color::LIGHT_BLUE);
      $line = trim(fgets(STDIN));
      echo Color::RESET;

      if (is_null($attemptsLeft))
      {
        $isValid = true;
      }
      else
      {
        if(empty($line) && !empty($defaultValue))
        {
          $line = $defaultValue;
        }

        --$attemptsLeft;
        if (!empty($line))
        {
          $isValid = true;
        }
        else if ($attemptsLeft === 0)
        {
          exit(1);
        }
        else
        {
          printf("%sInvalid input: %d attempts left%s\n", Color::MAGENTA, $attemptsLeft, Color::RESET);
        }
      }
    }
    while(!$isValid);

    if (empty($line) && !is_null($defaultValue))
    {
      $line = $defaultValue;
    }

    return $line;
  }

  /**
   * @param string $message
   * @param int|null $attempts
   * @return string
   */
  public static function promptPassword(string $message = 'Password', ?int $attempts = null): string
  {
    # Turn echo off
    `/bin/stty -echo`;

    $line = self::prompt(message: $message, attempts: $attempts);

    # Turn echo no
    `/bin/stty echo`;
    echo "\n";

    return $line;
  }

  /**
   * @param string $message
   * @param bool $defaultYes
   * @return bool
   */
  public static function confirm(string $message, bool $defaultYes = true): bool
  {
    $suffix = $defaultYes ? 'Y/n' : 'y/N';
    $response = $defaultYes;
    $defaultHint = Color::DARK_WHITE . "($suffix) " . Color::RESET;

    printf("%s?%s %s: %s%s", Color::GREEN, Color::RESET, $message, $defaultHint, Color::LIGHT_BLUE);
    $line = trim(fgets(STDIN));

    if (!empty($line))
    {
      $response = match(strtolower($line)) {
        'yes',
        'y',
        'yeah',
        'yep',
        'correct',
        'true',
        'affirmative' => true,
        default       => false
      };
    }
    if ($response === $defaultYes)
    {
      self::cursor()::moveUpBy(numberOfLines: 1);
      self::eraser()::entireLine();
      $suffix = $defaultYes ? 'Y' : 'N';
      $defaultHint = Color::LIGHT_BLUE . "$suffix " . Color::RESET;
      printf("\r%s?%s %s: %s%s\n", Color::GREEN, Color::RESET, $message, $defaultHint, Color::LIGHT_BLUE);
    }
    echo Color::RESET;

    return $response;
  }

  /**
   * @return ConsoleCursor
   */
  public static function cursor(): ConsoleCursor
  {
    return new ConsoleCursor;
  }

  /**
   * @return ConsoleEraser
   */
  public static function eraser(): ConsoleEraser
  {
    return new ConsoleEraser;
  }

  /**
   * @return TermInfo
   */
  public static function termInfo(): TermInfo
  {
    return new TermInfo;
  }

  /**
   * @param mixed $obj
   * @return string
   */
  private static function objectToString(mixed $obj): string
  {
    return match (gettype($obj)) {
      'array',
      'object' => json_encode($obj, JSON_PRETTY_PRINT),
      'boolean',
      'integer',
      'double' => strval($obj),
      default => $obj
    };
  }

  /**
   * @param string $path
   * @param int|null $filesize
   * @return void
   */
  public static function logCreate(string $path, ?int $filesize = null): void
  {
    self::logFileAction(path: $path, filesize: $filesize);
  }

  /**
   * @param string $path
   * @param int|null $filesize
   * @return void
   */
  public static function logUpdate(string $path, ?int $filesize = null): void
  {
    self::logFileAction(action: self::FILE_UPDATE, path: $path, filesize: $filesize);
  }

  /**
   * @param string $path
   * @param string $to
   * @return void
   */
  public static function logRename(string $path, string $to): void
  {
    self::logFileAction(action: self::FILE_RENAME, path: sprintf("%s → %s", $path, $to));
  }

  /**
   * @param string $path
   * @return void
   */
  public static function logDelete(string $path): void
  {
    self::logFileAction(action: self::FILE_DELETE, path: $path);
  }

  /**
   * @param string $action
   * @param string $path
   * @param int|null $filesize
   * @return void
   */
  private static function logFileAction(string $action = self::FILE_CREATE, string $path = '', ?int $filesize = null): void
  {
    $colorCode = match($action) {
      self::FILE_CREATE => Color::GREEN,
      self::FILE_DELETE => Color::RED,
      self::FILE_RENAME,
      self::FILE_UPDATE => Color::LIGHT_BLUE,
      default             => Color::YELLOW
    };

    $bytes = Number::formatBytes(bytes: $filesize);
    $suffix = is_null($filesize) ? '' : " ($bytes)";

    printf("%s%s%s %s%s\n", $colorCode, $action, Color::RESET, $path, $suffix);
  }

  /**
   * @param ...$args
   * @return never
   */
  public static function debug(...$args): never
  {
    exit(var_export($args, true) . PHP_EOL);
  }
}