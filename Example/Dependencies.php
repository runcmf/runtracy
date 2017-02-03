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

$c = $app->getContainer();

// Twig
$c['twig_profile'] = function () {
    return new Twig_Profiler_Profile();
};

$c['view'] = function (\Slim\Container $c) {
    $settings = $c->get('settings')['view'];
    $view = new \Slim\Views\Twig($settings['template_path'], $settings['twig']);
    // Add extensions
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new Twig_Extension_Profiler($c['twig_profile']));
    $view->addExtension(new Twig_Extension_Debug());
    return $view;
};

// monolog
$c['logger'] = function (\Slim\Container $c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    $logger->pushHandler(new Monolog\Handler\RotatingFileHandler($settings['path'], $settings['maxFiles'], $settings['level']));
    return $logger;
};

//Override the default Not Found Handler
$c['notFoundHandler'] = function (\Slim\Container $c) {
    return function ($request, $response) use ($c) {
        $c->view->offsetSet('erno', '404');
        $c->view->offsetSet('ermes', 'Page not found');
        $c->view->offsetSet('uri', $request->getUri());
        return $c->view->render($response, 'Error/40x.html.twig')->withStatus(404);
    };
};
//Override the default Not Allowed Handler
$c['notAllowedHandler'] = function (\Slim\Container $c) {
    return function ($request, $response, $methods) use ($c) {
        $c->view->offsetSet('erno', '405');
        $c->view->offsetSet('ermes', 'Can not route with method{s}: ' . implode(', ', $methods));
        $c->view->offsetSet('uri', $request->getUri());
        return $c->view->render($response, 'Error/40x.html.twig')->withStatus(405);
    };
};

// Register Eloquent multiple connections
//PHP Notice: Indirect modification of overloaded element of Illuminate\Container\Container has no effect in .../vendor/illuminate/database/Capsule/Manager.php:51
//PHP Notice: Indirect modification of overloaded element of Illuminate\Container\Container has no effect in .../vendor/illuminate/database/Capsule/Manager.php:53
// https://github.com/illuminate/database/pull/194

//$elo_con = new \Illuminate\Container\Container();
//$elo_con['config'] = [
//    'database.connections' => $cfg['settings']['db']['connections'],
//    'database.default' => $cfg['settings']['db']['default'],
//    'database.fetch' => PDO::FETCH_OBJ// PDO::FETCH_ASSOC and so on
//];
//$capsule = new \Illuminate\Database\Capsule\Manager($elo_con);
//$capsule->setAsGlobal();
//$capsule->bootEloquent();
//$capsule::connection()->enableQueryLog();


// Register Eloquent single connections
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($cfg['settings']['db']['connections']['mysql']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$capsule::connection()->enableQueryLog();


// Both Doctrine DBAL & ORM
$c['dbal'] = function () {
    $conn = \Doctrine\DBAL\DriverManager::getConnection(
        [
            'driver' => 'pdo_mysql',
            'host' => '127.0.0.1',
            'user' => 'dbuser',
            'password' => '123',
            'dbname' => 'bookshelf',
            'port' => 3306,
            'charset' => 'utf8',
        ],
        new \Doctrine\DBAL\Configuration
    );
    // possible return or DBAL\Query\QueryBuilder or DBAL\Connection
    return $conn->createQueryBuilder();
};

// this example from https://github.com/vhchung/slim3-skeleton-mvc
// doctrine EntityManager
$c['em'] = function ($c) {
    $settings = $c->get('settings');
    $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
        $settings['doctrine']['meta']['entity_path'],
        $settings['doctrine']['meta']['auto_generate_proxies'],
        $settings['doctrine']['meta']['proxy_dir'],
        $settings['doctrine']['meta']['cache'],
        false
    );
    // possible return or ORM\EntityManager or ORM\QueryBuilder
    return \Doctrine\ORM\EntityManager::create($settings['doctrine']['connection'], $config);
};


