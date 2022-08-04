<?php

namespace Assegai\Cli\Interfaces;

interface IBuildable
{
  public function build(object $options): void;
}
