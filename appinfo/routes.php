<?php

return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'settings#save', 'url' => '/settings', 'verb' => 'POST'],
        ['name' => 'settings#testConnection', 'url' => '/settings/test', 'verb' => 'POST'],
    ],
];