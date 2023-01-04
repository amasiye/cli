<?php

namespace Assegai\Cli\Interfaces;

interface IExecutable
{
  public function getId(): string;

  public function configure(): void;

  public function execute(IArgumentHost $context): int;

  public function undo(IArgumentHost $context): int;
}