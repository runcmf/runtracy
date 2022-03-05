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

namespace Tests\RunTracy\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Tests\BaseTestCase;
use RunTracy\Middlewares\TracyMiddleware;

/**
 * @runTestsInSeparateProcesses
 * Class TracyMiddlewareTest
 * @package Tests\RunTracy\Middlewares
 */
class TracyMiddlewareTest extends BaseTestCase
{
    /**
     * @covers \RunTracy\Middlewares\TracyMiddleware
     */
    public function testTracyMiddleware()
    {
        $cookie = 'bar=foo; foo=bar; tracyPanelsEnabled=';
        // set all possible panels
        $cookie .= '["showPhpInfoPanel","showSlimRouterPanel","showSlimEnvironmentPanel",' .
            '"showSlimRequestPanel","showSlimResponsePanel","showSlimContainer",' .
            '"showEloquentORMPanel","showTwigPanel","showProfilerPanel","showVendorVersionsPanel"' .
            ',"showXDebugHelper","showIncludedFiles","showConsolePanel","showIdiormPanel","showDoctrinePanel"]';

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI'    => '/hello/World',
                'HTTP_COOKIE'    => $cookie,// to TracyMiddleware
                'HTTP_CONTENT_TYPE' => 'text/html; charset=utf-8'// Tracy check content-type
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();

        $app = new \Slim\App($this->cfg);

        $c = $app->getContainer();
        // Twig
        $c['twig_profile'] = function () {
            return new \Twig_Profiler_Profile();
        };
        $c['view'] = function ($c) {
            $settings = $c->get('settings')['view'];
            $view = new \Slim\Views\Twig($settings['template_path'], $settings['twig']);
            // Add extensions
            $view->addExtension(new \Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
            $view->addExtension(new \Twig_Extension_Profiler($c['twig_profile']));
            $view->addExtension(new \Twig_Extension_Debug());
            return $view;
        };
        // Register Eloquent single connections
        $capsule = new \Illuminate\Database\Capsule\Manager();
        $capsule->addConnection($this->cfg['settings']['db']['connections']['mysql']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $capsule::connection()->enableQueryLog();

        $c['dbal'] = function () {
            $conn = \Doctrine\DBAL\DriverManager::getConnection(
                $this->getDoctrineDBALConfig(),
                new \Doctrine\DBAL\Configuration()
            );
            // return DBAL\Connection
            return $conn;
        };
        $this->assertInstanceOf('\Doctrine\DBAL\Connection', $c->get('dbal'));

        $app->add(new TracyMiddleware($app));

        $app->get('/hello/{name}', function (Request $request, Response $response) {
            return '
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
            </head>
            <body>
                <div>Hello, ' . $request->getAttribute('name') . '</div>
            </body>
        </html>';
        });

        // Process the application
        $response = $app->process($request, $response);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertRegexp('/Hello\, World/s', (string)$response->getBody());

        // try unset Manager
        $capsule = new \Illuminate\Database\Capsule\Manager();
        unset($capsule);
        // check all work
        $response = $app->process($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertRegexp('/Hello\, World/s', (string)$response->getBody());

        // try unset container
        unset($c['dbal']);
        // check all work
        $response = $app->process($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertRegexp('/Hello\, World/s', (string)$response->getBody());
    }

    public function testTracyMiddlewareWithoutCookies()
    {
        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI'    => '/hello/World',
                'HTTP_COOKIE'    => '',
                'HTTP_CONTENT_TYPE' => 'text/html; charset=utf-8'
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();

        $app = new \Slim\App($this->cfg);
        $c = $app->getContainer();

        $app->add(new TracyMiddleware($app));

        $app->get('/hello/{name}', function (Request $request, Response $response) {
            return '
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
            </head>
            <body>
                <div>Hello, ' . $request->getAttribute('name') . '</div>
            </body>
        </html>';
        });

        // Process the application
        $response = $app->process($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertRegexp('/Hello\, World/s', (string)$response->getBody());
    }

    public function testTracyMiddlewareDirectly()
    {
        $app = new \Slim\App();

        $mid = new \RunTracy\Middlewares\TracyMiddleware();

        $this->assertInstanceOf('\RunTracy\Middlewares\TracyMiddleware', $mid);

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI'    => '/hello/World',
                'HTTP_COOKIE'    => '',// to TracyMiddleware
                'HTTP_CONTENT_TYPE' => 'text/html; charset=utf-8'// Tracy check content-type
            ]
        );
        $req = \Slim\Http\Request::createFromEnvironment($environment);
        $res = new \Slim\Http\Response();

        $this->assertNull($mid->__invoke($req, $res, $this->fakeReturn($req, $res)));
//fwrite(STDERR, '$mid: ' . var_export($mid->__invoke($req, $res, $this->fakeReturn()), true) . " ###\n");
        //__invoke(Request $request, Response $response, callable $next)


//fwrite(STDERR, '$mid: ' . var_export($foo->invokeArgs($obj, [$req, $res]), true) . " ###\n");
    }

    public function testRunCollectorsReturnNull()
    {
        $foo = self::getMethod('runCollectors');
        $obj = new TracyMiddleware();

        $this->assertNull($foo->invokeArgs($obj, []));
    }

    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('\RunTracy\Middlewares\TracyMiddleware');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    protected function fakeReturn(Request $req, Response $res)
    {
        return function ($req, $res) {
            return null;
        };
    }

    protected function getDoctrineDBALConfig()
    {
        return [
            'driver' => 'pdo_mysql',
            'host' => '127.0.0.1',
            'user' => 'dbuser',
            'password' => '123',
            'dbname' => 'bookshelf',
            'port' => 3306,
            'charset' => 'utf8',
        ];
    }
}
