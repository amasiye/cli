<?php

namespace Assegai\Cli\Enumerations;

enum TestType: string
{
  case ACCEPTANCE = 'acceptance';
  case API = 'api';
  case FUNCTIONAL = 'functional';
  case UNIT = 'unit';
}
