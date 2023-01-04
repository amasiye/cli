<?php

namespace Assegai\Cli\Enumerations;

enum ValueType: string
{
  case STRING = 'string';
  case INTEGER = 'integer';
  case FLOAT = 'double';
  case ARRAY = 'array';
  case OBJECT = 'object';
  case CALLABLE = 'callable';
  case BOOLEAN = 'boolean';
  case ENUM = 'enum';
}