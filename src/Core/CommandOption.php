<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Enumerations\ValueRequirementType;

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
   */
  public function __construct(
    public readonly string  $name,
    public readonly ?string $alias = null,
    public readonly ValueRequirementType $type = ValueRequirementType::NOT_ALLOWED
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
}