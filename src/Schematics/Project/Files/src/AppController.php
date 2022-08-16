<?php

namespace Assegai\App;

use Assegai\Core\Attributes\Controller;
use Assegai\Core\Attributes\Http\Get;

#[Controller(path: '')]
class AppController
{
  public function __construct(private readonly AppService $appService)
  {    
  }

  #[Get]
  function index(): string
  {
    return $this->appService->getHome();
  }
}