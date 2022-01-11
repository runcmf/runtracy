<?php

declare(strict_types=1);

namespace RunTracy\Middlewares;

use Exception;
use Illuminate\Database\Capsule\Manager;
use RunTracy\Collectors\DoctrineCollector;
use RunTracy\Collectors\IdormCollector;
use RunTracy\Helpers\ConsolePanel;
use RunTracy\Helpers\DoctrinePanel;
use RunTracy\Helpers\EloquentORMPanel;
use RunTracy\Helpers\IdiormPanel;
use RunTracy\Helpers\IncludedFiles;
use RunTracy\Helpers\PhpInfoPanel;
use RunTracy\Helpers\ProfilerPanel;
use RunTracy\Helpers\SlimContainerPanel;
use RunTracy\Helpers\SlimEnvironmentPanel;
use RunTracy\Helpers\SlimRequestPanel;
use RunTracy\Helpers\SlimResponsePanel;
use RunTracy\Helpers\SlimRouterPanel;
use RunTracy\Helpers\TwigPanel;
use RunTracy\Helpers\VendorVersionsPanel;
use RunTracy\Helpers\XDebugHelper;
use Slim\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Tracy\Debugger;
use RunTracy\Helpers\PanelSelector;
use Tracy\Dumper;

/**
 * Class TracyMiddleware
 * @package RunTracy\Middlewares
 */
class TracyMiddleware implements MiddlewareInterface
{
    private $container;
    private $defcfg;
    private $versions;
    private $routeCollector;

    /**
     * @throws Exception
     */
    public function __construct(App $app = null)
    {
        include_once realpath(__DIR__ . '/../') . '/shortcuts.php';

        if ($app instanceof App) {
            $this->container = $app->getContainer();
            $this->versions = [
                'slim' => App::VERSION,
            ];
            $this->defcfg = $this->container->has('settings.tracy')
                ? $this->container->get('settings.tracy') : $this->container->get('settings')['tracy'];

            $this->routeCollector = $app->getRouteCollector();

            $this->runCollectors();
        }
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $cookies = $request->getCookieParams();
        if (isset($cookies['tracyPanelsEnabled'])) {
            $cookies = json_decode($cookies['tracyPanelsEnabled']);
        }else{
            $cookies = [];
        }

        if (!empty($cookies)) {
            $def = array_fill_keys(array_keys($this->defcfg), null);
            $cookies = array_fill_keys($cookies, 1);
            $cfg = array_merge($def, $cookies);
        } else {
            $cfg = $this->defcfg;
        }

        if (isset($cfg['showEloquentORMPanel']) && $cfg['showEloquentORMPanel']) {
            if (class_exists('\Illuminate\Database\Capsule\Manager')) {
                Debugger::getBar()->addPanel(new EloquentORMPanel(
                    Manager::getQueryLog(),
                    $this->versions
                ));
            } else {
                // do not show in panel selector
                unset($this->defcfg['showEloquentORMPanel']);
            }
        }
        if (isset($cfg['showTwigPanel']) && $cfg['showTwigPanel']) {
            Debugger::getBar()->addPanel(new TwigPanel(
                $this->container->get('twig_profile'),
                $this->versions
            ));
        }
        if (isset($cfg['showPhpInfoPanel']) && $cfg['showPhpInfoPanel']) {
            Debugger::getBar()->addPanel(new PhpInfoPanel());
        }
        if (isset($cfg['showSlimEnvironmentPanel']) && $cfg['showSlimEnvironmentPanel']) {
            Debugger::getBar()->addPanel(new SlimEnvironmentPanel(
                Dumper::toHtml($request->getServerParams()),
                $this->versions
            ));
        }
        if (isset($cfg['showSlimContainer']) && $cfg['showSlimContainer']) {
            Debugger::getBar()->addPanel(new SlimContainerPanel(
                Dumper::toHtml($this->container),
                $this->versions
            ));
        }

        if (isset($cfg['showSlimRouterPanel']) && $cfg['showSlimRouterPanel']) {
            Debugger::getBar()->addPanel(new SlimRouterPanel(
                $this->routeCollector->getRoutes(),
                $this->versions
            ));
        }

        if (isset($cfg['showSlimRequestPanel']) && $cfg['showSlimRequestPanel']) {
            Debugger::getBar()->addPanel(new SlimRequestPanel(
                Dumper::toHtml($handler),
                $this->versions
            ));
        }
        if (isset($cfg['showSlimResponsePanel']) && $cfg['showSlimResponsePanel']) {
            Debugger::getBar()->addPanel(new SlimResponsePanel(
                Dumper::toHtml($response),
                $this->versions
            ));
        }
        if (isset($cfg['showVendorVersionsPanel']) && $cfg['showVendorVersionsPanel']) {
            Debugger::getBar()->addPanel(new VendorVersionsPanel());
        }
        if (isset($cfg['showXDebugHelper']) && $cfg['showXDebugHelper']) {
            Debugger::getBar()->addPanel(new XDebugHelper(
                $this->defcfg['configs']['XDebugHelperIDEKey']
            ));
        }
        if (isset($cfg['showIncludedFiles']) && $cfg['showIncludedFiles']) {
            Debugger::getBar()->addPanel(new IncludedFiles());
        }
        // check if enabled or blink if active critical value
        if ((isset($cfg['showConsolePanel']) && $cfg['showConsolePanel']) || isset($cfg['configs']['ConsoleNoLogin']) && $cfg['configs']['ConsoleNoLogin']) {
            Debugger::getBar()->addPanel(new ConsolePanel(
                $this->defcfg['configs']
            ));
        }
        if (isset($cfg['showProfilerPanel']) && $cfg['showProfilerPanel']) {
            Debugger::getBar()->addPanel(new ProfilerPanel(
                $this->defcfg['configs']['ProfilerPanel']
            ));
        }
        if (isset($cfg['showIdiormPanel']) && $cfg['showIdiormPanel']) {
            Debugger::getBar()->addPanel(new IdiormPanel(
                $this->versions
            ));
        }
        if (isset($cfg['showDoctrinePanel']) && $cfg['showDoctrinePanel']) {
            if (class_exists('\Doctrine\DBAL\Connection') && $this->container->has('doctrineConfig')) {
                Debugger::getBar()->addPanel(new DoctrinePanel(
                    $this->container->get('doctrineConfig')->getSQLLogger()->queries,
                    $this->versions
                ));
            } else {
                // do not show in panel selector
                unset($this->defcfg['showDoctrinePanel']);
            }
        }

        // hardcoded without config prevent switch off
        if (!isset($this->defcfg) && !is_array($this->defcfg)) {
            $this->defcfg = [];
        }
        Debugger::getBar()->addPanel(new PanelSelector(
            $cfg,
            array_diff_key($this->defcfg, ['configs' => null])
        ));

        return $response;
    }

    /**
     * @throws Exception
     */
    private function runCollectors()
    {
        if (isset($this->defcfg['showIdiormPanel']) && $this->defcfg['showIdiormPanel'] > 0) {
            if (class_exists('\ORM')) {
                // no return values
                new IdormCollector();
            }
        }

        if (isset($this->defcfg['showDoctrinePanel']) && class_exists('\Doctrine\DBAL\Connection')) {
            new DoctrineCollector(
                $this->container,
                $this->defcfg['showDoctrinePanel']
            );
        }
    }
}
