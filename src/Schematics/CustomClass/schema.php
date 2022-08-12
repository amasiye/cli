<?php

return [
  'properties' => [
    'name' => [
      'description' => 'The name of the new class.',
      'type' => 'string',
      'pattern' => "^(?:@[a-zA-Z0-9-*~][a-zA-Z0-9-*._~]*/)?[a-zA-Z0-9-~][a-zA-Z0-9-._~]*$",
      'default' => [
        'source' => 'argv',
        'index' => 0,
      ],
      'x-prompt' => "What name would you like to use for the class?",
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
  ]
];