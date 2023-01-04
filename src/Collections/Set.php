<?php

namespace Assegai\Cli\Collections;

use ArrayAccess;
use ArrayIterator;
use Traversable;

/**
 *
 */
class Set extends AbstractCollection implements ArrayAccess
{
  /**
   * The minimum allowable capacity of the collection container.
   */
  const MIN_CAPACITY = 16;

  /**
   * @var int
   */
  protected int $_capacity = self::MIN_CAPACITY;

  /**
   * @param array $values
   */
  public function __construct(array $values = [])
  {
    $excess = $this->capacity() - count($values);
    $shouldAllocate = $excess < 1;
    if ($shouldAllocate)
    {
      $this->allocate(capacity: self::MIN_CAPACITY);
    }
    else
    {
      $this->values = $values;
    }
  }

  /**
   * @inheritDoc
   */
  public function getIterator(): Traversable
  {
    return new ArrayIterator($this->values);
  }

  /**
   * @inheritDoc
   */
  public function count(): int
  {
    return count($this->values);
  }

  /**
   * @inheritDoc
   */
  public function jsonSerialize(): string|bool
  {
    return json_encode($this->values);
  }

  /**
   * @param mixed $offset
   * @return bool
   */
  public function offsetExists(mixed $offset): bool
  {
    return key_exists($offset, $this->values);
  }

  /**
   * @param mixed $offset
   * @return mixed
   */
  public function offsetGet(mixed $offset): mixed
  {
    return $this->values[$offset];
  }

  /**
   * @param mixed $offset
   * @param mixed $value
   * @return void
   */
  public function offsetSet(mixed $offset, mixed $value): void
  {
    $this->values[$offset] = $value;
  }

  /**
   * @param mixed $offset
   * @return void
   */
  public function offsetUnset(mixed $offset): void
  {
    unset($this->values[$offset]);
  }

  /**
   * Adds values to the set.
   * Adds all given values to the set that haven't already been added.
   * @param mixed ...$values
   * @return void
   */
  public function add(mixed ...$values): void
  {
    foreach ($values as $value)
    {
      $index = array_search($value, $this->values);
      if ($index === false)
      {
        $this->values[] = $value;
      }
      else
      {
        $this->values[$index] = $value;
      }
    }
  }

  /**
   * Allocates enough memory for a required capacity.
   * @param int $capacity
   * @return void
   */
  public function allocate(int $capacity): void
  {
    $this->_capacity = max($capacity, self::MIN_CAPACITY);
  }

  /**
   * Returns the current capacity
   *
   * @return int
   */
  public function capacity(): int
  {
    return $this->_capacity;
  }

  /**
   * Determines if the set contains all values.
   * @param mixed ...$values
   * @return bool
   */
  public function contains(mixed ...$values): bool
  {
    foreach ($values as $value)
    {
      if (!in_array($value, $this->values))
      {
        return false;
      }
    }

    return true;
  }

  /**
   * @return $this
   */
  public function copy(): self
  {
    $copy = new self();
    // TODO: Implement copy()
    return $copy;
  }

  /**
   * @param Set $set
   * @return $this
   */
  public function diff(self $set): self
  {
    $diff = array_diff($this->values, $set->toArray());
    return new self($diff);
  }

  /**
   * @param callable|null $callback
   * @return $this
   */
  public function filter(?callable $callback = null): self
  {
    $filtered = array_filter($this->values, $callback);
    return new self($filtered);
  }

  /**
   * @return mixed
   */
  public function first(): mixed
  {
    $firstKey = array_key_first($this->values);
    return $this->get($firstKey);
  }

  /**
   * @param int $index
   * @return mixed
   */
  public function get(int $index): mixed
  {
    return $this->values[$index] ?? null;
  }

  /**
   * @param Set $set
   * @return $this
   */
  public function intersect(self $set): self
  {
    // TODO: Implement intersect()
    return $this;
  }

  /**
   * @param string|null $glue
   * @return string
   */
  public function join(?string $glue = ', '): string
  {
    return implode($glue, $this->values);
  }

  /**
   * @return mixed
   */
  public function last(): mixed
  {
    $lastKey = array_key_last($this->values);
    return $this->values[$lastKey];
  }

  /**
   * @param mixed $values
   * @return $this
   */
  public function merge(mixed $values): self
  {
    // TODO: Implement merge()
    return new self();
  }

  /**
   * @param callable $callback
   * @param mixed|null $initial
   * @return mixed
   */
  public function reduce(callable $callback, mixed $initial = null): mixed
  {
    // TODO: Implement reduce()
    return null;
  }

  /**
   * @param mixed ...$values
   * @return void
   */
  public function remove(mixed ...$values): void
  {
    foreach ($values as $value)
    {
      if ($key = array_search($value, $this->values) )
      {
        unset($this->values[$key]);
      }
    }
  }

  /**
   * @return void
   */
  public function reverse(): void
  {
    $values = array_reverse($this->values);
    $this->values = $values;
  }

  /**
   * @return $this
   */
  public function reversed(): self
  {
    $this->reverse();
    return $this;
  }

  /**
   * @param int $index
   * @param int|null $length
   * @return $this
   */
  public function slice(int $index, ?int $length = null): self
  {
    $slice = array_slice($this->values, $index, $length);
    return new self($slice);
  }

  /**
   * @param callable|null $comparator
   * @return void
   */
  public function sort(?callable $comparator = null): void
  {
    if ($comparator)
    {
      $sortedValues = array_map($comparator, $this->values);
      $this->values = $sortedValues;
    }
    else
    {
      sort($this->values);
    }
  }

  /**
   * @param callable|null $comparator
   * @return $this
   */
  public function sorted(?callable $comparator = null): self
  {
    $this->sort($comparator);
    return $this;
  }

  /**
   * @return int|float
   */
  public function sum(): int|float
  {
    return array_sum($this->values);
  }

  /**
   * @param Set $set
   * @return $this
   */
  public function union(self $set): self
  {
    $union = array_merge($this->values, $set->toArray());
    return new self($union);
  }

  /**
   * @param Set $set
   * @return $this
   */
  public function xor(self $set): self
  {
    $xor = array_diff($this->values, $set->toArray());
    return new self($xor);
  }
}