<?php

$c = $app->getContainer();

// Twig
$c['twig_profile'] = function () {
    return new Twig_Profiler_Profile();
};

$c['view'] = function ($c) {
    $settings = $c->get('settings')['view'];
    $view = new \Slim\Views\Twig($settings['template_path'], $settings['twig']);
    // Add extensions
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new Twig_Extension_Profiler($c['twig_profile']));
    $view->addExtension(new Twig_Extension_Debug());
    return $view;
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
