<?php

namespace Assegai\Cli\Interfaces;

interface ISchematic
{
  public function build(object $options): void;
}
