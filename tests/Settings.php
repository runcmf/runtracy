<?php
/**
 * Copyright 2016 1f7.wizard@gmail.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

//date_default_timezone_set('Europe/Moscow');
date_default_timezone_set('UTC');

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
define('DIR', realpath(__DIR__ . '/../../') . DS);

return [
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
        'addContentLengthHeader' => false,// if true = Unexpected data in output buffer
//        'routerCacheFile' => DIR . 'var/cache/fastroute.cache',//TODO uncomment after debug
        'siteUrl' => 'https://run.dev',

        'db' => [// database configuration
            'default' => 'mysql',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => DIR . 'var/database/database.sqlite',
                    'charset' => 'utf8',
                    'prefix' => 'mybb_',
                ],
                'mysql' => [
                    'driver' => 'mysql',
//                    'engine' => 'MyISAM',
                    'engine' => 'InnoDB',
                    'host' => '127.0.0.1',
                    'database' => 'fakedb',
                    'username' => 'dbuser',
                    'password' => '123',
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => 'mybb_'
                ],
                'pgsql' => [
                    'driver' => 'pgsql',
                    'host' => '127.0.0.1',
                    'database' => 'fakedb',
                    'username' => 'dbuser',
                    'password' => '123',
                    'charset' => 'utf8',
                    'prefix' => 'mybb_',
                    'schema' => 'public',
                ],
                'sqlsrv' => [
                    'driver' => 'sqlsrv',
                    'host' => '127.0.0.1',
                    'database' => 'fakedb',
                    'username' => 'root',
                    'password' => '',
                    'prefix' => '',
                ],
            ],
        ],
        'view' => [// Twig settings
            'template_path' => DIR . 'app/Views',
            'twig' => [
                'cache' => DIR . 'var/cache/twig',
                'debug' => true,
            ],
        ],
        'logger' => [// monolog settings
            'name' => 'RunCMF',
            'path' => DIR . 'var/log/app.log',
            'maxFiles' => 15
        ]
    ]
];