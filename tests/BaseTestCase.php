<?php

namespace Tests;

use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Uri;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * This is an example class that shows how you could set up a method that
 * runs the application. Note that it doesn't cover all use-cases and is
 * tuned to the specifics of this skeleton app, so if your needs are
 * different, you'll need to change it.
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Use middleware when running application?
     *
     * @var bool
     */
    protected $withMiddleware = true;

    /**
     * Process the application given a request method and URI
     *
     * @param string            $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string            $requestUri    the request URI
     * @param array|object|null $requestData   the request data
     *
     * @return \Slim\Http\Response
     */
    public function runApp($requestMethod, $requestUri, $requestData = null)
    {
        $requestMethod = strtoupper($requestMethod);
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI'    => $requestUri,
//                'SCRIPT_NAME' => '/app.php',
            ]
        );
        $_SESSION = [];
        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);
        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }
        // Set up a response object
        $response = new Response();
        // Use the application settings
        $cfg = require __DIR__ . '/../app/Config/Settings.php';
        // Instantiate the application
        $app = new App($cfg);
        // Set up dependencies
        require __DIR__ . '/../app/Config/Dependencies.php';
        // Register middleware
        require __DIR__ . '/../app/Config/Middleware.php';
        // Register routes
        require __DIR__ . '/../app/Config/Routes.php';
        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }

    public function runApp2($requestMethod, $requestUri, $requestData = null)
    {
        $requestMethod = strtoupper($requestMethod);
        $app = new App();
        $app->get($requestUri, function ($req, $res) use ($app) {
//            return $this->router->pathFor('index');
//            return $app->get('/zzz', 'App\Controllers\Home:index')->setName('homepage');
            return;
        });

        $env = Environment::mock([
//            'SCRIPT_NAME' => '/app.php',
//            'SERVER_NAME' => 'run.dev',
            'REQUEST_URI' => $requestUri,
            'REQUEST_METHOD' => $requestMethod,
//            'QUERY_STRING' => 'abc=123&foo=bar',
//            'CONTENT_TYPE' => 'application/json;charset=utf8',
        ]);

        $uri          = Uri::createFromEnvironment($env);
        $headers      = Headers::createFromEnvironment($env);
        $cookies      = [];
        $serverParams = $env->all();
        $body         = new Body(fopen('php://temp', 'r+'));
        $req          = new Request($requestMethod, $uri, $headers, $cookies, $serverParams, $body);
        $res          = new Response();

        return $app($req, $res);
    }
}
