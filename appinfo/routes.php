<?php

return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'settings#get', 'url' => '/admin/settings', 'verb' => 'GET'],
        ['name' => 'settings#save', 'url' => '/admin/settings', 'verb' => 'POST'],
        ['name' => 'settings#testConnection', 'url' => '/admin/settings/test', 'verb' => 'POST'],
    ],
];