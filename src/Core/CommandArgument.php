<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Enumerations\ValueType;
use Assegai\Cli\Exceptions\InvalidArgumentException;

/**
 *
 */
final class CommandArgument
{
  /**
   * @var mixed|null
   */
  private mixed $value = null;

  /**
   * @param string $name
   * @param string|null $alias
   * @param bool $isRequired
   * @param string|null $description
   * @param ValueType $valueType
   * @param mixed|false $defaultValue
   * @param string|null $enum
   * @throws InvalidArgumentException
   */
  public function __construct(
    public readonly string $name,
    public readonly ?string $alias = null,
    public readonly bool $isRequired = false,
    public readonly ?string $description = null,
    public readonly ValueType $valueType = ValueType::BOOLEAN,
    public mixed $defaultValue = null,
    public readonly ?string $enum = null,
  )
  {
    if (!$this->defaultValue)
    {
      $this->defaultValue = match ($this->valueType) {
        ValueType::STRING => '',
        ValueType::INTEGER, ValueType::FLOAT => 0,
        ValueType::ARRAY => [],
        ValueType::ENUM => $this->enum::cases()[0] ?? null,
        ValueType::CALLABLE => null,
        default => false
      };
    }
    $this->validateValue($this->defaultValue);
    $this->setValue($this->defaultValue);
  }

  /**
   * @param string $name
   * @return bool
   */
  public function hasName(string $name): bool
  {
    return in_array($name, [$this->name, $this->alias]);
  }

  /**
   * @return mixed|null
   */
  public function getValue(): mixed
  {
    return $this->value;
  }

  /**
   * @param mixed|null $value
   * @throws InvalidArgumentException
   */
  public function setValue(mixed $value): void
  {
    $this->validateValue($value);
    $this->value = $value;
  }

  /**
   * @param mixed $value
   * @return void
   * @throws InvalidArgumentException
   */
  private function validateValue(mixed $value): void
  {
    if (is_null($value))
    {
      return;
    }

    if ($this->valueType !== ValueType::ENUM)
    {
      $isValid = match ($this->valueType) {
        ValueType::STRING => is_string($value),
        ValueType::INTEGER => is_int($value),
        ValueType::FLOAT => is_float($value),
        ValueType::BOOLEAN => is_bool($value),
        ValueType::ARRAY => is_array($value),
        ValueType::CALLABLE => is_callable($value),
        default => is_object($value)
      };
    }
    else
    {
      // Handle enum
      if ( ! enum_exists($this->enum) )
      {
        throw new InvalidArgumentException("$this->enum is not an ENUM");
      }

      if (is_string($value) && ! in_array($value, $this->enum::values()))
      {
        throw new InvalidArgumentException("$value is not a valid case for $this->enum");
      }

      if (is_object($value) && ! in_array($value, $this->enum::cases()))
      {
        throw new InvalidArgumentException("$value->value is not a valid case for $this->enum");
      }

      $isValid = true;
    }

    if (! $isValid )
    {
      Console::error(obj: "Invalid command argument value $value for $this->name of type " . $this->valueType->value, exit: true);
    }
  }
}