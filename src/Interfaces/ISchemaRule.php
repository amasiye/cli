<?php

namespace Assegai\Cli\Interfaces;

use Assegai\Cli\Schematics\Tree;

interface ISchemaRule
{
  public function apply(): Tree;
}