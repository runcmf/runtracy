<?php

namespace Tests;

/**
 * Class BaseTestCase
 * @package Tests
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    public $cfg = null;

    public function setUp()
    {
        $this->cfg = require __DIR__ . '/Settings.php';
    }

    public function tearDown()
    {
//        ob_end_clean();
//fwrite(STDERR, '$panel->getTab(): ' . $panel->getTab() . " ###\n");
    }

    protected function callProtectedMethod($name, $classname, $params)
    {
        $class = new \ReflectionClass($classname);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        $obj = new $classname($params);
        return $method->invokeArgs($obj, $params);
    }

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
        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI'    => $requestUri,
            ]
        );
        $_SESSION = [];
        // Set up a request object based on the environment
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }
        // Set up a response object
        $response = new \Slim\Http\Response();
        // Use the application settings
        $cfg = require __DIR__ . '/Settings.php';
        // Instantiate the application
        $app = new \Slim\App($cfg);
        // Set up dependencies
//        require __DIR__ . '/../app/Config/Dependencies.php';
        // Register middleware
//        require __DIR__ . '/../app/Config/Middleware.php';
        // Register routes
//        require __DIR__ . '/../app/Config/Routes.php';
        $app->post('/console', 'RunTracy\Controllers\RunTracyConsole:index');
        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }
}
