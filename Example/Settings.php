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

use Tracy\Debugger;

date_default_timezone_set('Europe/Moscow');
//date_default_timezone_set('UTC');

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
define('DIR', realpath(__DIR__ . '/../../') . DS);

Debugger::enable(Debugger::DEVELOPMENT, DIR . 'var/log');
//Debugger::enable(Debugger::PRODUCTION, DIR . 'var/log');
Debugger::timer();

return [
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
        'addContentLengthHeader' => false,// if true = Unexpected data in output buffer
//        'routerCacheFile' => DIR . 'var/cache/fastroute.cache',// uncomment after debug

        'db' => [// multi database configuration
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
                    'database' => 'run',
                    'username' => 'dbuser',
                    'password' => '123',
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => 'mybb_'
                ],
                'pgsql' => [
                    'driver' => 'pgsql',
                    'host' => '127.0.0.1',
                    'database' => 'run',
                    'username' => 'dbuser',
                    'password' => '123',
                    'charset' => 'utf8',
                    'prefix' => 'mybb_',
                    'schema' => 'public',
                ],
                'sqlsrv' => [
                    'driver' => 'sqlsrv',
                    'host' => '127.0.0.1',
                    'database' => 'database',
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
            'level' => \Monolog\Logger::DEBUG,
            'path' => DIR . 'var/log/app.log',
            'maxFiles' => 15
        ],
        'modules' => [// register modules
            'forum' => 'RunBB\Init'
        ],
        'tracy' => [
            'showPhpInfoPanel' => 0,
            'showSlimRouterPanel' => 0,
            'showSlimEnvironmentPanel' => 0,
            'showSlimRequestPanel' => 1,
            'showSlimResponsePanel' => 1,
            'showSlimContainer' => 0,
            'showEloquentORMPanel' => 0,
            'showTwigPanel' => 0,
            'showProfilerPanel' => 0,
            'showVendorVersionsPanel' => 0,
            'showXDebugHelper' => 0,
            'showIncludedFiles' => 0,
            'showConsolePanel' => 0,
            'configs' => [
                // XDebugger IDE key
                'XDebugHelperIDEKey' => 'PHPSTORM',
                // Disable login (don't ask for credentials, be careful) values ( 1 | 0 )
                'ConsoleNoLogin' => 0,
                // Multi-user credentials values( ['user1' => 'password1', 'user2' => 'password2'] )
                'ConsoleAccounts' => [
                    'dev' => '34c6fceca75e456f25e7e99531e2425c6c1de443'// = sha1('dev')
                ],
                // Password hash algorithm (password must be hashed) values('md5', 'sha256' ...)
                'ConsoleHashAlgorithm' => 'sha1',
                // Home directory (multi-user mode supported) values ( var || array )
                // '' || '/tmp' || ['user1' => '/home/user1', 'user2' => '/home/user2']
                'ConsoleHomeDirectory' => DIR,
                // terminal.js full URI
                'ConsoleTerminalJs' => '/assets/js/jquery.terminal.min.js',
                // terminal.css full URI
                'ConsoleTerminalCss' => '/assets/css/jquery.terminal.min.css',
                'ProfilerPanel' => [
                    // Memory usage 'primaryValue' set as Profiler::enable() or Profiler::enable(1)
//                    'primaryValue' =>                   'effective',    // or 'absolute'
                    'show' => [
                        'memoryUsageChart' => 1, // or false
                        'shortProfiles' => true, // or false
                        'timeLines' => true // or false
                    ]
                ]
            ]
        ]
    ]
];
