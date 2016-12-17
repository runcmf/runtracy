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

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');
ini_set('log_errors', 'On');

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

/**
 * Step 1: Require the Slim Framework using Composer's autoloader
 *
 * If you are not using Composer, you need to load Slim Framework with your own
 * PSR-4 autoloader.
 */
require __DIR__.'/../vendor/autoload.php';
RunTracy\Helpers\Profiler\Profiler::enable();

RunTracy\Helpers\Profiler\Profiler::start('loadSettings');
$cfg = require __DIR__ . '/../app/Config/Settings.php';
RunTracy\Helpers\Profiler\Profiler::finish('loadSettings');

RunTracy\Helpers\Profiler\Profiler::start('initApp');
$app = new Slim\App($cfg);
RunTracy\Helpers\Profiler\Profiler::finish('initApp');

RunTracy\Helpers\Profiler\Profiler::start('RegisterDependencies');
// Register dependencies
require __DIR__ . '/../app/Config/Dependencies.php';
RunTracy\Helpers\Profiler\Profiler::finish('RegisterDependencies');

RunTracy\Helpers\Profiler\Profiler::start('RegisterMiddlewares');
// Register middleware
require __DIR__ . '/../app/Config/Middleware.php';
RunTracy\Helpers\Profiler\Profiler::finish('RegisterMiddlewares');

RunTracy\Helpers\Profiler\Profiler::start('RegisterRoutes');
// Register routes
require __DIR__ . '/../app/Config/Routes.php';
RunTracy\Helpers\Profiler\Profiler::finish('RegisterRoutes');

RunTracy\Helpers\Profiler\Profiler::start('RegisterModules');
// Register modules
$app->getContainer()->get('module')->initModules($app, $cfg['settings']['modules']);
RunTracy\Helpers\Profiler\Profiler::finish('RegisterModules');

RunTracy\Helpers\Profiler\Profiler::start('runApp, %s, line %s', basename(__FILE__), __LINE__);
// Run app
$app->run();
RunTracy\Helpers\Profiler\Profiler::finish('runApp, %s, line %s', basename(__FILE__), __LINE__);