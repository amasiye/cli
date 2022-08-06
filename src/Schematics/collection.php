<?php

use Assegai\Cli\Enumerations\SchematicType;
use Assegai\Cli\Util\Text;

function classifySchemaName($name): string
{
  $output = Text::kebabToPascal($name);
  return sprintf("%sSchematic", $output);
}

$schematicTypes = SchematicType::cases();
$schema = [];

foreach ($schematicTypes as $type)
{
  $schema[$type->value] = 'Assegai\Cli\Schematics\Module\\' . classifySchemaName($type->value);
}

return [
  'schema' => $schema
];