<?php

namespace Assegai\Cli\Interfaces;

interface ISchema
{
  public function build(object $options): void;
}
