<?php

namespace Tests;

/**
 * Class BaseTestCase
 * @package Tests
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    public $cfg = NULL;

    public function setUp() {
        $this->cfg = require __DIR__ . '/Settings.php';
    }

    public function tearDown() {
//        ob_end_clean();
//fwrite(STDERR, '$panel->getTab(): ' . $panel->getTab() . " ###\n");
    }

    protected function callProtectedMethod($name, $classname, $params) {
        $class = new \ReflectionClass($classname);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        $obj = new $classname($params);
        return $method->invokeArgs($obj, $params);
    }
}
