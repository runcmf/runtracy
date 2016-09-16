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
$elo_con = new \Illuminate\Container\Container();
$elo_con['config'] = [
    'database.connections' => $cfg['settings']['db']['connections'],
    'database.default' => $cfg['settings']['db']['default'],
    'database.fetch' => PDO::FETCH_OBJ
];
$capsule = new \Illuminate\Database\Capsule\Manager($elo_con);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$capsule::connection()->enableQueryLog();
