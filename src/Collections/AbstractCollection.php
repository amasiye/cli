<?php

namespace Assegai\Cli\Collections;

use Assegai\Cli\Interfaces\ICollection;

/**
 * AbstractCollection is the base class which covers functionality common to all the data structures in this library.
 * It guarantees that all structures are traversable, countable, and can be converted to json using json_encode().
 */
abstract class AbstractCollection implements ICollection
{
  /**
   * @param array $values
   */
  public function __construct(protected array $values = [])
  {
  }

  /**
   * Removes all values.
   * @return void
   */
  public function clear(): void
  {
    $this->values = [];
  }

  /**
   * Returns a shallow copy of the collection.
   * @return $this
   */
  public function copy(): self
  {
    return new self::class($this->values);
  }

  /**
   * Returns whether the collection is empty.
   * @return bool
   */
  public function isEmpty(): bool
  {
    return empty($this->values);
  }

  /**
   * Converts the collection to an array.
   * @return array
   */
  public function toArray(): array
  {
    return $this->values;
  }
}