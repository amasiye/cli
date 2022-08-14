<?php

namespace Assegai\Cli\Interfaces;

use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 *
 */
interface ICollection extends Countable, IteratorAggregate, JsonSerializable
{
  /**
   * @return void
   */
  public function clear(): void;

  /**
   * @return $this
   */
  public function copy(): self;

  /**
   * @return bool
   */
  public function isEmpty(): bool;

  /**
   * @return array
   */
  public function toArray(): array;
}