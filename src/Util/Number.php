<?php

namespace Assegai\Cli\Util;

class Number
{
  public function __construct(public readonly string|int|float $value)
  {
  }

  public function __toString(): string
  {
    return (string)$this->value;
  }

  public static function formatBytes(?int $bytes): string
  {
    if (is_null($bytes))
    {
      $bytes = 0;
    }
    return match (true) {
      $bytes < 1048576 => number_format($bytes / 1024, 2) . " KB",
      $bytes < 1073741824 => number_format($bytes / 1048576, 2) . " MB",
      $bytes < 1099511627776 => number_format($bytes / 1048576, 2) . " GB",
      default => "$bytes Bytes"
    };
  }
}