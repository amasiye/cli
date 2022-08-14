<?php

namespace Assegai\Cli\Core\Console;

final class TermInfo
{
  public static function windowSize(): Rect
  {
    return new Rect(width: exec('tput cols'), height: exec('tput lines'));
  }
}