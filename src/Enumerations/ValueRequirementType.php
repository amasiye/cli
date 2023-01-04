<?php

namespace Assegai\Cli\Enumerations;

enum ValueRequirementType
{
  case NOT_ALLOWED;
  case REQUIRED;
  case OPTIONAL;
}