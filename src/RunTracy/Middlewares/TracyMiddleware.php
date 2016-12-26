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
use RunTracy\Helpers\PanelSelector;

/**
 * Class TracyMiddleware
 * @package RunTracy\Middlewares
 */
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
        $ver = [
            'slim' => \Slim\App::VERSION,
        ];

        $defcfg = $this->app->getContainer()['settings']['tracy'];
        $cookies = json_decode($request->getCookieParam('tracyPanelsEnabled'));
        if (!empty($cookies)) {
            $def = array_fill_keys(array_keys($defcfg), null);
            $cookies = array_fill_keys($cookies, 1);
            $cfg = array_merge($def, $cookies);
        } else {
            $cfg = [];
        }
        if (isset($cfg['showEloquentORMPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\EloquentORMPanel(
                \Illuminate\Database\Capsule\Manager::getQueryLog()
            ));
        }
        if (isset($cfg['showTwigPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\TwigPanel(
                $this->app->getContainer()->get('twig_profile')
            ));
        }
        if (isset($cfg['showPhpInfoPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\PhpInfoPanel());
        }
        if (isset($cfg['showSlimEnvironmentPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimEnvironmentPanel(
                \Tracy\Dumper::toHtml($this->app->getContainer()->get('environment')),
                $ver
            ));
        }
        if (isset($cfg['showSlimContainer'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimContainerPanel(
                \Tracy\Dumper::toHtml($this->app->getContainer()),
                $ver
            ));
        }
        if (isset($cfg['showSlimRouterPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimRouterPanel(
                \Tracy\Dumper::toHtml($this->app->getContainer()->get('router')),
                $ver
            ));
        }
        if (isset($cfg['showSlimRequestPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimRequestPanel(
                \Tracy\Dumper::toHtml($this->app->getContainer()->get('request')),
                $ver
            ));
        }
        if (isset($cfg['showSlimResponsePanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimResponsePanel(
                \Tracy\Dumper::toHtml($this->app->getContainer()->get('response')),
                $ver
            ));
        }
        if (isset($cfg['showVendorVersionsPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\VendorVersionsPanel());
        }
        if (isset($cfg['showXDebugHelper'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\XDebugHelper(
                $defcfg['configs']['XDebugHelperIDEKey']
            ));
        }
        if (isset($cfg['showIncludedFiles'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\IncludedFiles());
        }
        // check if enabled or blink if active critical value
        if (isset($cfg['showConsolePanel']) || $defcfg['configs']['ConsoleNoLogin']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\ConsolePanel(
                $defcfg['configs']
            ));
        }
        if (isset($cfg['showProfilerPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\ProfilerPanel(
                $defcfg['configs']['ProfilerPanel']
            ));
        }

        // hardcoded without config prevent switch off
        Debugger::getBar()->addPanel(new PanelSelector($cfg, array_diff_key($defcfg, ['configs' => null])));

        return $res;
    }
}
