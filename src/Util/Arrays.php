<?php

namespace Assegai\Cli\Util;

class Arrays
{
  private function __construct()
  {}

  public static function printArray(array $input): string
  {
    $output = json_encode($input, JSON_PRETTY_PRINT);

    $output = str_replace('{', '[', $output);
    $output = str_replace('}', ']', $output);
    return str_replace('":', '" =>', $output);
  }
}