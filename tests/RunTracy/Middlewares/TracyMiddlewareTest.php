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

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Tests\BaseTestCase;

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
        $cookie .= '["showPhpInfoPanel","showSlimRouterPanel","showSlimEnvironmentPanel",'.
            '"showSlimRequestPanel","showSlimResponsePanel","showSlimContainer",'.
            '"showEloquentORMPanel","showTwigPanel","showProfilerPanel","showVendorVersionsPanel"'.
            ',"showXDebugHelper","showIncludedFiles","showConsolePanel"]';

        // Create a mock environment for testing with
        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI'    => '/hello/World',
                'HTTP_COOKIE'    => $cookie,// to TracyMiddleware
                'HTTP_CONTENT_TYPE' => 'text/html; charset=utf-8'// Tracy check content-type
            ]
        );
//        $_SESSION = [];
        // Set up a request object based on the environment
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        // Set up a response object
        $response = new \Slim\Http\Response();

        // Use the application settings
//        $cfg = require __DIR__ . '/Settings.php';
        // Instantiate the application
        $app = new \Slim\App($this->cfg);
        // Set up dependencies
//        require __DIR__ . '/../app/Config/Dependencies.php';
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
        $capsule = new \Illuminate\Database\Capsule\Manager;
        $capsule->addConnection($this->cfg['settings']['db']['connections']['mysql']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $capsule::connection()->enableQueryLog();

        // Register middleware
//        require __DIR__ . '/../app/Config/Middleware.php';
        $app->add(new \RunTracy\Middlewares\TracyMiddleware($app));

        // Register routes
//        require __DIR__ . '/../app/Config/Routes.php';
        $app->get('/hello/{name}', function (Request $request, Response $response) {
            return '
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
            </head>
            <body>
                <div>Hello, '.$request->getAttribute('name').'</div>
            </body>
        </html>';
        });

        // Process the application
        $response = $app->process($request, $response);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertRegexp('/Hello\, World/s', (string)$response->getBody());
    }
}
