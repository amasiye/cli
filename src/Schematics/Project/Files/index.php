<?php

use Assegai\Core\AssegaiFactory;
use Assegai\App\AppModule;

require './vendor/autoload.php';

function bootstrap(): void
{
  $app = AssegaiFactory::create(AppModule::class);
  $app->run();
}

bootstrap();