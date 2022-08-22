<?php

namespace Assegai\App;

use Assegai\Core\Attributes\Injectable;

#[Injectable]
class AppService
{
  public function getHome(): string
  {
    return "Muli bwanji!";
  }
}