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

namespace RunTracy\Middlewares;

use Tracy\Debugger;
use RunTracy\Helpers\EloquentORMPanel;
use RunTracy\Helpers\TwigPanel;
use RunTracy\Helpers\PhpInfoPanel;
use RunTracy\Helpers\SlimRouterPanel;
use RunTracy\Helpers\SlimEnvironmentPanel;
use RunTracy\Helpers\SlimRequestPanel;
use RunTracy\Helpers\SlimResponsePanel;

use Illuminate\Database\Capsule\Manager as DB;

class TracyMiddleware
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function __invoke($request, $response, $next)
    {
        $res = $next($request, $response);

//        $d = DB::getQueryLog();
//        $p = $this->app->getContainer()->get('twig_profile');
        $v = [
            'slim' => \Slim\App::VERSION,
            'twig' => \Twig_Environment::VERSION
        ];

        $cfg = $this->app->getContainer()->get('settings')['tracy'];
        if ($cfg['showEloquentORMPanel']) {
            Debugger::getBar()->addPanel(new EloquentORMPanel(DB::getQueryLog(), $v));
        }
        if ($cfg['showTwigPanel']) {
            Debugger::getBar()->addPanel(new TwigPanel($this->app->getContainer()->get('twig_profile'), $v));
        }
        if ($cfg['showRawEloquentORMLog']) {
            Debugger::barDump(DB::getQueryLog(), 'RAW Eloquent ORM log');
        }
        if ($cfg['showRawTwigProfiler']) {
            Debugger::barDump($this->app->getContainer()->get('twig_profile'), 'RAW Twig Profiler');
        }
        if ($cfg['showRawSlimContainer']) {
            Debugger::barDump($this->app->getContainer(), 'RAW Slim Container');
        }
        if ($cfg['showPhpInfoPanel']) {
            Debugger::getBar()->addPanel(new PhpInfoPanel());
        }
        if ($cfg['showSlimEnvironmentPanel']) {
            Debugger::getBar()->addPanel(new SlimEnvironmentPanel($this->app, $v));
        }
        if ($cfg['showSlimRouterPanel']) {
            Debugger::getBar()->addPanel(new SlimRouterPanel($this->app, $v));
        }
        if ($cfg['showSlimRequestPanel']) {
            Debugger::getBar()->addPanel(new SlimRequestPanel($this->app, $v));
        }
        if ($cfg['showSlimResponsePanel']) {
            Debugger::getBar()->addPanel(new SlimResponsePanel($this->app, $v));
        }

        return $res;
    }
}