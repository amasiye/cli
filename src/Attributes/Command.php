<?php

namespace Assegai\Cli\Attributes;

use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Core\CommandOption;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Command
{
  const SUCCESS = 0;
  const ERROR_DEFAULT = 1;

  /**
   * @param string $name
   * @param string|null $usage
   * @param string|null $shortName
   * @param string $description
   * @param string|null $longDescription
   * @param CommandOption[] $options
   * @param CommandArgument[] $arguments
   */
  public final function __construct(
    public readonly string  $name,
    public readonly ?string  $usage = null,
    public readonly ?string $shortName = null,
    public readonly string  $description = '',
    public readonly ?string $longDescription = null,
    public readonly array $options = [],
    public readonly array $arguments = [],
  )
  {
  }
}