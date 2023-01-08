<?php

namespace Assegai\App\%== __path@namespacify__ ==%;

use Assegai\Core\Interfaces\ICanActivate;
use Assegai\Core\Interfaces\IExecutionContext;
use Assegai\Core\Attributes\Injectable;

#[Injectable]
class %== __name@pascalize__ ==%Guard implements ICanActivate
{
  public function canActivate(IExecutionContext $context): bool
  {
    // TODO: Implement %== __name@pascalize__ ==%::canActivate() method.
    return true;
  }
}