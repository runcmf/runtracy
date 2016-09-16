# Slim Framework Tracy Adater #

# Install
**1**
``` bash
$ composer require runcmf/runtracy
```

**2** add to your dependencies Twig, Twig_Profiler, Eloquent ORM like this
```php
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
$capsule::connection()->enableQueryLog();//necessary for debugging
```

**3** register Middleware
``` php
$app->add(new \RunTracy\Middlewares\TracyDBMiddleware($app));
```

**4** add to your settings
``` php
use Tracy\Debugger;

Debugger::enable(Debugger::DEVELOPMENT);
//Debugger::enable(Debugger::PRODUCTION);
Debugger::dispatch();
```
see examples in RunTracy/Example



### Who do I talk to? ###

* 1f7.wizard ( at ) gmail.com
* http://runcmf.ru

## License

Apache License
Version 2.0. Please see [License File](LICENSE.md) for more information.