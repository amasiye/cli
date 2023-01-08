<?php

namespace Assegai\App;

use Assegai\Core\Attributes\Controller;
use Assegai\Core\Attributes\Http\Get;
use Assegai\Core\Rendering\View;

#[Controller(path: '')]
class AppController
{
  public function __construct(private readonly AppService $appService)
  {    
  }

  #[Get]
  function index(): View
  {
    return $this->appService->getHome();
  }
}