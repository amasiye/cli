<?php

namespace Assegai\Cli\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Command
{
  const SUCCESS = 0;
  const ERROR_DEFAULT = 1;

  public final function __construct(
    public readonly string  $name,
    public readonly string  $usage,
    public readonly ?string $shortName = null,
    public readonly string  $description = '',
    public readonly ?string $longDescription = null,
  )
  {
  }
}