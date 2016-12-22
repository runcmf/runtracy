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
}
