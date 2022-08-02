<?php

namespace Assegai\Cli\Core\Console;

use Assegai\Cli\Enumerations\Color\Color;

class Console
{
  public static function assert(): void
  {
    // TODO: Implement assert()
  }

  public static function print(string $message, string $color = Color::RESET): void
  {
    printf("%s%s%s", $color, $message, Color::RESET);
  }

  public static function log(string $message, bool $exit = false): void
  {
    if ($exit)
    {
      exit($message . PHP_EOL);
    }

    echo $message . PHP_EOL;
  }

  public static function info(mixed $obj, bool $exit = false): void
  {
    self::log(message: sprintf("%s%s%s", Color::BLUE, self::objectToString($obj), Color::RESET), exit: $exit);
  }

  public static function warn(mixed $obj, bool $exit = false): void
  {
    self::log(message: sprintf("%s%s%s", Color::YELLOW, self::objectToString($obj), Color::RESET), exit: $exit);
  }

  public static function error(mixed $obj, bool $exit = false): void
  {
    self::log(message: sprintf("%s%s%s", Color::RED, self::objectToString($obj), Color::RESET), exit: $exit);
  }

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
}