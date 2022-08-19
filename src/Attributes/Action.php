<?php

namespace Assegai\Cli\Attributes;

use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Core\CommandOption;
use Attribute;
use stdClass;

#[Attribute(Attribute::TARGET_METHOD)]
class Action
{
  public stdClass $args;

  /**
   * @param string|null $name
   * @param string|null $alias
   * @param string $description
   * @param CommandOption[] $options
   * @param CommandArgument[] $arguments
   */
  public function __construct(
    public readonly ?string $name = null,
    public readonly ?string $alias = null,
    public readonly string $description = '',
    public readonly array $options = [],
    public readonly array $arguments = []
  )
  {
  }
}