<?php

namespace Assegai\App;

use Assegai\Core\Attributes\Injectable;
use Assegai\Core\Rendering\View;

#[Injectable]
class AppService
{
  public function getHome(): View
  {
    return new View('index', [
      'title' => 'Muli Bwanji',
      'subtitle' => 'AssegaiPHP',
      'welcomeLink' => 'https://assegaiphp.ml/',
      'getStartedLink' => 'https://assegaiphp.ml/getting-started/',
      'documentationLink' => 'https://docs.assegaiphp.ml/',
      'donateLink' => 'https://donate.assegaiphp.ml/',
    ]);
  }
}