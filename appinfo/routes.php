<?php

return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'settings#get', 'url' => '/api/v1/admin/settings', 'verb' => 'GET'],
        ['name' => 'settings#save', 'url' => '/api/v1/admin/settings', 'verb' => 'POST'],
        ['name' => 'settings#testConnection', 'url' => '/api/v1/admin/settings/test', 'verb' => 'POST'],
        ['name' => 'settings#testApiKey', 'url' => '/api/v1/admin/settings/test-api-key', 'verb' => 'POST'],
        ['name' => 'settings#deleteConfigValue', 'url' => '/api/v1/admin/settings/config-value', 'verb' => 'DELETE']
    ],
];
