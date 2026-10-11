<?php

return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'settings#get', 'url' => '/api/admin/settings', 'verb' => 'GET'],
        ['name' => 'settings#save', 'url' => '/api/admin/settings', 'verb' => 'POST'],
        ['name' => 'settings#testConnection', 'url' => '/api/admin/settings/test', 'verb' => 'POST'],
        ['name' => 'settings#testApiKey', 'url' => '/api/admin/settings/test-api-key', 'verb' => 'POST'],
    ],
];
