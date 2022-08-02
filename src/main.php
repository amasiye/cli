#!/usr/bin/env php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Assegai\Cli\Commands\GenerateCommand;
use Assegai\Cli\Commands\HelpCommand;
use Assegai\Cli\Commands\InfoCommand;
use Assegai\Cli\Commands\ListCommand;
use Assegai\Cli\Commands\ServeCommand;
use Assegai\Cli\Commands\UpdateCommand;
use Assegai\Cli\Commands\VersionCommand;
use Assegai\Cli\Core\App;
use Assegai\Cli\Core\AssegaiCliFactory;

function bootstrap(): void
{
  $app = AssegaiCliFactory::create(App::class);

  $app
    ->addAll([
      new GenerateCommand(),
      new HelpCommand(),
      new InfoCommand(),
      new ListCommand(),
      new ServeCommand(),
      new UpdateCommand(),
      new VersionCommand(),
    ])
    ->run();
}

bootstrap();