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
ini_set('display_errors', 1);

/**
 * Step 1: Require the Slim Framework using Composer's autoloader
 *
 * If you are not using Composer, you need to load Slim Framework with your own
 * PSR-4 autoloader.
 */
require __DIR__.'/../vendor/autoload.php';

$cfg = require __DIR__ . '/../app/Config/Settings.php';

$app = new Slim\App($cfg);

// Register dependencies
require __DIR__ . '/../app/Config/Dependencies.php';

// Register middleware
require __DIR__ . '/../app/Config/Middleware.php';

// Register routes
require __DIR__ . '/../app/Config/Routes.php';

// Run app
$app->run();