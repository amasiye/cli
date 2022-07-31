#!/usr/bin/env php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Assegai\Cli\Core\App;
use Assegai\Cli\Core\AssegaiCliFactory;

function bootstrap(): void
{
  $app = AssegaiCliFactory::create(App::class);
  $app->run();
}

bootstrap();