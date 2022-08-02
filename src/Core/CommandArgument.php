<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Enumerations\ValueType;

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
   */
  public function __construct(
    public readonly string $name,
    public readonly ?string $alias = null,
    public readonly bool $isRequired = false,
    public readonly ?string $description = null,
    public readonly ValueType $valueType = ValueType::BOOLEAN,
    public readonly mixed $defaultValue = false,
    public readonly ?string $enum = null,
  )
  {
    if ($this->valueType === ValueType::ENUM)
    {
      // TODO: Validate default value against enum::cases()
    }
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
   */
  public function setValue(mixed $value): void
  {
    $this->value = $value;
  }
}