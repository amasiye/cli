<?php

namespace Assegai\Cli\Interfaces;

interface IExecutable
{
  public function getId(): string;

  public function execute(IArgumentHost $context): int;

  public function undo(IArgumentHost $context): int;
}