<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Enumerations\ValueType;

/**
 *
 */
final class CommandOption
{
  /**
   * @var mixed|null
   */
  private mixed $value = null;

  /**
   * @param string $name
   * @param string|null $alias
   * @param ValueRequirementType $type
   * @param string|null $description
   * @param ValueType $valueType
   * @param mixed|null $defaultValue
   */
  public function __construct(
    public readonly string  $name,
    public readonly ?string $alias = null,
    public readonly ValueRequirementType $type = ValueRequirementType::NOT_ALLOWED,
    public readonly ?string $description = null,
    public readonly ValueType $valueType = ValueType::STRING,
    public readonly mixed $defaultValue = null
  )
  {
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

  public function acceptsValue(): bool
  {
    return in_array($this->type, [ValueRequirementType::REQUIRED, ValueRequirementType::OPTIONAL]);
  }
}