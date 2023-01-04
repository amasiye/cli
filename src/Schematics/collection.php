<?php

use Assegai\Cli\Enumerations\SchematicType;
use Assegai\Cli\Util\Text;

function classifySchemaName($name): string
{
  $output = Text::pascalize($name);
  return sprintf("%sSchematic", $output);
}

$schematicTypes = SchematicType::cases();
$schema = [];

foreach ($schematicTypes as $type)
{
  $schema[$type->value] = 'Assegai\Cli\Schematics\\' . Text::pascalize($type->value) . '\\' . classifySchemaName($type->value);
}

return ['schema' => $schema];