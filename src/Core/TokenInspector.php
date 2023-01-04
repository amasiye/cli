<?php

namespace Assegai\Cli\Core;

final class TokenInspector
{
  private static ?self $instance = null;

  private function __construct()
  {
  }

  public static function getInstance(): self
  {
    if (! self::$instance )
    {
      self::$instance = new self;
    }

    return self::$instance;
  }

  public function isShortOption(string $token): bool
  {
    // TODO: Implement isShortOption()
    return true;
  }

  public function isLongOption(string $token): bool
  {
    // TODO: Implement isShortOption()
    return true;
  }
}