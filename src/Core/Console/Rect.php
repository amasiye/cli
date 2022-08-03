<?php

namespace Assegai\Cli\Core\Console;

/**
 *
 */
class Rect
{
  /**
   * @param int|float $width
   * @param int|float $height
   */
  public function __construct(
    private readonly int|float $width = 0,
    private readonly int|float $height = 0,
  ) { }

  /**
   * @return int|float
   */
  public function width(): int|float { return $this->width; }

  /**
   * @return int|float
   */
  public function height(): int|float { return $this->height; }
}