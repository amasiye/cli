<?php

namespace Assegai\Cli\Core;

class AssegaiCliFactory
{
  public static function create(string $appClassName, array|object|null $config = null): App
  {
    $app = new $appClassName();

    if ($config)
    {
      $app = $app->setConfig($config);
    }

    return $app;
  }
}