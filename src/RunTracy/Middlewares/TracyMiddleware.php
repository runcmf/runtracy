<?php

declare(strict_types=1);

namespace RunTracy\Middlewares;

use Slim\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Tracy\Debugger;
use RunTracy\Helpers\PanelSelector;

/**
 * Class TracyMiddleware
 * @package RunTracy\Middlewares
 */
class TracyMiddleware implements MiddlewareInterface
{
    private $container;
    private $defcfg;
    private $versions;

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
                Debugger::getBar()->addPanel(new \RunTracy\Helpers\EloquentORMPanel(
                    \Illuminate\Database\Capsule\Manager::getQueryLog()
                ));
            } else {
                // do not show in panel selector
                unset($this->defcfg['showEloquentORMPanel']);
            }
        }
        if (isset($cfg['showTwigPanel']) && $cfg['showTwigPanel']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\TwigPanel(
                $this->container->get('twig_profile')
            ));
        }
        if (isset($cfg['showPhpInfoPanel']) && $cfg['showPhpInfoPanel']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\PhpInfoPanel());
        }
        if (isset($cfg['showSlimEnvironmentPanel']) && $cfg['showSlimEnvironmentPanel']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimEnvironmentPanel(
                \Tracy\Dumper::toHtml($request->getServerParams()),
                $this->versions
            ));
        }
        if (isset($cfg['showSlimContainer']) && $cfg['showSlimContainer']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimContainerPanel(
                \Tracy\Dumper::toHtml($this->container),
                $this->versions
            ));
        }

        if (isset($cfg['showSlimRouterPanel']) && $cfg['showSlimRouterPanel']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimRouterPanel(
                \Tracy\Dumper::toHtml($request->getAttribute('routingResults')->getDispatcher()),
                $this->versions
            ));
        }

        if (isset($cfg['showSlimRequestPanel']) && $cfg['showSlimRequestPanel']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimRequestPanel(
                \Tracy\Dumper::toHtml($handler),
                $this->versions
            ));
        }
        if (isset($cfg['showSlimResponsePanel']) && $cfg['showSlimResponsePanel']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\SlimResponsePanel(
                \Tracy\Dumper::toHtml($response),
                $this->versions
            ));
        }
        if (isset($cfg['showVendorVersionsPanel']) && $cfg['showVendorVersionsPanel']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\VendorVersionsPanel());
        }
        if (isset($cfg['showXDebugHelper']) && $cfg['showXDebugHelper']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\XDebugHelper(
                $this->defcfg['configs']['XDebugHelperIDEKey']
            ));
        }
        if (isset($cfg['showIncludedFiles']) && $cfg['showIncludedFiles']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\IncludedFiles());
        }
        // check if enabled or blink if active critical value
        if ((isset($cfg['showConsolePanel']) && $cfg['showConsolePanel']) || isset($cfg['configs']['ConsoleNoLogin']) && $cfg['configs']['ConsoleNoLogin']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\ConsolePanel(
                $this->defcfg['configs']
            ));
        }
        if (isset($cfg['showProfilerPanel']) && $cfg['showProfilerPanel']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\ProfilerPanel(
                $this->defcfg['configs']['ProfilerPanel']
            ));
        }
        if (isset($cfg['showIdiormPanel']) && $cfg['showIdiormPanel']) {
            Debugger::getBar()->addPanel(new \RunTracy\Helpers\IdiormPanel());
        }
        if (isset($cfg['showDoctrinePanel']) && $cfg['showDoctrinePanel']) {
            if (class_exists('\Doctrine\DBAL\Connection') && $this->container->has('doctrineConfig')) {
                Debugger::getBar()->addPanel(
                    new \RunTracy\Helpers\DoctrinePanel(
                        $this->container->get('doctrineConfig')->getSQLLogger()->queries
                    )
                );
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

    private function runCollectors()
    {
        if (isset($this->defcfg['showIdiormPanel']) && $this->defcfg['showIdiormPanel'] > 0) {
            if (class_exists('\ORM')) {
                // no return values
                new \RunTracy\Collectors\IdormCollector();
            }
        }

        if (isset($this->defcfg['showDoctrinePanel']) && class_exists('\Doctrine\DBAL\Connection')) {
            new \RunTracy\Collectors\DoctrineCollector(
                $this->container,
                $this->defcfg['showDoctrinePanel']
            );
        }
    }
}
