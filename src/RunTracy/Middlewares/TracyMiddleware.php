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
//use RunTracy\Helpers\EloquentORMPanel;
//use RunTracy\Helpers\TwigPanel;
//use RunTracy\Helpers\PhpInfoPanel;
//use RunTracy\Helpers\SlimContainerPanel;
//use RunTracy\Helpers\SlimRouterPanel;
//use RunTracy\Helpers\SlimEnvironmentPanel;
//use RunTracy\Helpers\SlimRequestPanel;
//use RunTracy\Helpers\SlimResponsePanel;
//use RunTracy\Helpers\VendorVersionsPanel;
//use RunTracy\Helpers\XDebugHelper;
//use RunTracy\Helpers\IncludedFiles;
use RunTracy\Helpers\PanelSelector;

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

        $v = [
            'slim' => \Slim\App::VERSION,
        ];

        $defcfg = $this->app->getContainer()->get('settings')['tracy'];
//        $cookies = $request->getCookieParam('tracyPanelsEnabled', []);
        // SLim cut array or json cookie https://github.com/slimphp/Slim/issues/2101
        $cookies = isset($_COOKIE['tracyPanelsEnabled']) ? json_decode($_COOKIE['tracyPanelsEnabled']) : [];
        if(!empty($cookies)) {
            $def = array_fill_keys(array_keys($defcfg), null);
            $cookies = array_fill_keys($cookies, 1);
            $cfg = array_merge($def, $cookies);
        } else {
            $cfg = [];
        }
        if (isset($cfg['showEloquentORMPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\EloquentORMPanel(DB::getQueryLog()));
        }
        if (isset($cfg['showTwigPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\TwigPanel($this->app->getContainer()->get('twig_profile')));
        }
        if (isset($cfg['showPhpInfoPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\PhpInfoPanel());
        }
        if (isset($cfg['showSlimEnvironmentPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimEnvironmentPanel($this->app, $v));
        }
        if (isset($cfg['showSlimContainer'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimContainerPanel($this->app, $v));
        }
        if (isset($cfg['showSlimRouterPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimRouterPanel($this->app, $v));
        }
        if (isset($cfg['showSlimRequestPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimRequestPanel($this->app, $v));
        }
        if (isset($cfg['showSlimResponsePanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimResponsePanel($this->app, $v));
        }
        if (isset($cfg['showVendorVersionsPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\VendorVersionsPanel( ));
        }
        if (isset($cfg['showXDebugHelper'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\XDebugHelper( $defcfg['configs']['XDebugHelperIDEKey'] ));
        }
        if (isset($cfg['showIncludedFiles'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\IncludedFiles( ));
        }
        // check if enabled or blink if active critical value
        if (isset($cfg['showConsolePanel']) || $defcfg['configs']['ConsoleNoLogin']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\ConsolePanel( $defcfg['configs'] ));
        }
        if (isset($cfg['showProfilerPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\ProfilerPanel( $defcfg['configs']['ProfilerPanel'] ));
        }

        // hardcoded without config prevent switch off
        Debugger::getBar()->addPanel(new PanelSelector( $cfg, array_diff_key($defcfg, ['configs' => null]) ));

        return $res;
    }
}