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

use Slim\App;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Tracy\Debugger;
use RunTracy\Helpers\PanelSelector;

/**
 * Class TracyMiddleware
 * @package RunTracy\Middlewares
 */
class TracyMiddleware
{
    private $container;
    private $defcfg;
    private $versions;

    public function __construct(App $app = null)
    {
        $this->container = $app->getContainer();
        $this->versions = [
            'slim' => App::VERSION,
        ];
        $this->defcfg = $this->container['settings']['tracy'];
        $this->runCollectors();
    }

    /**
     * @param $request \Psr\Http\Message\RequestInterface
     * @param $response \Psr\Http\Message\ResponseInterface
     * @param $next Callable
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $res = $next($request, $response);

        $cookies = json_decode($request->getCookieParam('tracyPanelsEnabled'));
        if (!empty($cookies)) {
            $def = array_fill_keys(array_keys($this->defcfg), null);
            $cookies = array_fill_keys($cookies, 1);
            $cfg = array_merge($def, $cookies);
        } else {
            $cfg = [];
        }
        if (isset($cfg['showEloquentORMPanel'])) {
            if (class_exists('\Illuminate\Database\Capsule\Manager')) {
                Debugger::getBar()->addPanel(new \RunTracy\Helpers\EloquentORMPanel(
                    \Illuminate\Database\Capsule\Manager::getQueryLog()
                ));
            } else {
                // do not show in panel selector
                unset($this->defcfg['showEloquentORMPanel']);
            }
        }
        if (isset($cfg['showTwigPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\TwigPanel(
                $this->container['twig_profile']
            ));
        }
        if (isset($cfg['showPhpInfoPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\PhpInfoPanel());
        }
        if (isset($cfg['showSlimEnvironmentPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimEnvironmentPanel(
                \Tracy\Dumper::toHtml($this->container['environment']),
                $this->versions
            ));
        }
        if (isset($cfg['showSlimContainer'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimContainerPanel(
                \Tracy\Dumper::toHtml($this->container),
                $this->versions
            ));
        }
        if (isset($cfg['showSlimRouterPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimRouterPanel(
                \Tracy\Dumper::toHtml($this->container['router']),
                $this->versions
            ));
        }
        if (isset($cfg['showSlimRequestPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimRequestPanel(
                \Tracy\Dumper::toHtml($this->container['request']),
                $this->versions
            ));
        }
        if (isset($cfg['showSlimResponsePanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimResponsePanel(
                \Tracy\Dumper::toHtml($this->container['response']),
                $this->versions
            ));
        }
        if (isset($cfg['showVendorVersionsPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\VendorVersionsPanel());
        }
        if (isset($cfg['showXDebugHelper'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\XDebugHelper(
                $this->defcfg['configs']['XDebugHelperIDEKey']
            ));
        }
        if (isset($cfg['showIncludedFiles'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\IncludedFiles());
        }
        // check if enabled or blink if active critical value
        if (isset($cfg['showConsolePanel']) || $this->defcfg['configs']['ConsoleNoLogin']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\ConsolePanel(
                $this->defcfg['configs']
            ));
        }
        if (isset($cfg['showProfilerPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\ProfilerPanel(
                $this->defcfg['configs']['ProfilerPanel']
            ));
        }
        if (isset($cfg['showIdiormPanel'])) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\IdiormPanel());
        }
        if (isset($cfg['showDoctrineDBALPanel'])) {
            if (class_exists('\Doctrine\DBAL\Logging\DebugStack')) {
                Debugger::getBar()->addPanel(
                    new \RunTracy\Helpers\DoctrineDBALPanel(
                        $this->container['dbalLogger']->getSQLLogger()->queries
                    )
                );
            } else {
                // do not show in panel selector
                unset($this->defcfg['showDoctrineDBALPanel']);
            }
        }

        // hardcoded without config prevent switch off
        Debugger::getBar()->addPanel(new PanelSelector(
            $cfg,
            array_diff_key($this->defcfg, ['configs' => null])
        ));

        return $res;
    }

    private function runCollectors()
    {
        if ($this->defcfg['showIdiormPanel'] > 0) {
            if (class_exists('\ORM')) {
                // no return values
                new \RunTracy\Collectors\IdormCollector();
            }
        }
    }
}
