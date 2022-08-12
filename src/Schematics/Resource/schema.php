<?php

return [
  'properties' => [
    'name' => [
      'description' => 'The name of the new resource.',
      'type' => 'string',
      'pattern' => "^(?:@[a-zA-Z0-9-*~][a-zA-Z0-9-*._~]*/)?[a-zA-Z0-9-~][a-zA-Z0-9-._~]*$",
      'default' => [
        'source' => 'argv',
        'index' => 0,
      ],
      'x-prompt' => 'What name would you like to use for the resource (plural, e.g., "users")?',
    ],
    'path' => [
      'description' => 'The path where the schema files will be generated.',
      'type' => 'string',
      'pattern' => "/^(\/?\w+\/?)+$/",
      'default' => [
        'source' => 'argv',
        'index' => 1,
      ]
    ],
    'transport' => [
      'description' => '',
      'type' => 'string',
      'x-prompt' => 'What transport layer do you use?'
    ],
    'generate-crud' => [
      'description' => 'Determines whether to generate CRUD entry points.',
      'type' => 'boolean',
      'default' => true,
      'x-prompt' => 'Would you like to generate CRUD entry points?'
    ],
    'singular' => [
      'description' => 'The singular version of the name.',
      'type' => 'string',
    ],
    'updateModule' => [
      'path' => '/AppModule.php',
      'imports' => ['__name@pascalize__Module::class'],
    ]
  ]
];