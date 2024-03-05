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

namespace Tests\RunTracy\Helpers;

use Tests\BaseTestCase;

/**
 * @runTestsInSeparateProcesses
 * Class VendorVersionsPanelTest
 * @package Tests\RunTracy\Helpers
 */
class VendorVersionsPanelTest extends BaseTestCase
{
    public function testVendorVersionsPanel()
    {
        $panel = new \RunTracy\Helpers\VendorVersionsPanel();
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

        // test Tracy tab
        $this->assertRegexp('/Vendor Versions/s', $panel->getTab());
        // test Tracy panel
        $this->assertRegexp('/VendorVersionsPanel/s', $panel->getPanel());
        unset($panel);
    }

    public function testVendorVersionsPanelWithData()
    {
        $path = realpath(__DIR__ . '/../../../');

        $panel = new \RunTracy\Helpers\VendorVersionsPanel($path);
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

        $this->assertRegexp('/phpunit\/php-code-coverage/s', $panel->getPanel());
    }

    public function testVendorVersionsPanelComposerDirectory()
    {
        // normal path, error empty
        $path = realpath(__DIR__ . '/../../../');
        $panel = new \RunTracy\Helpers\VendorVersionsPanel($path);
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);
        $this->assertEmpty($panel->getError());

        // fake path
        $panel = new \RunTracy\Helpers\VendorVersionsPanel('/fakePath/');
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);
        $this->assertEquals('Path "/fakePath/" is not a directory.', $panel->getError());

        // path without composer
        $path = realpath(__DIR__ . '/../../');
        $panel = new \RunTracy\Helpers\VendorVersionsPanel($path);
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);
        $this->assertStringEndsWith('does not contain the composer.lock file.', $panel->getError());
    }

    public function testVendorVersionsPanelDecode()
    {
        // normal file
        $path = realpath(__DIR__ . '/../../../tests/');
        $jsonFile = $path . DIRECTORY_SEPARATOR . 'testComposer.json';
        $lockFile = $path . DIRECTORY_SEPARATOR . 'testComposer.lock';

        $result = $this->callProtectedMethod('decode', '\RunTracy\Helpers\VendorVersionsPanel', [$jsonFile]);
        $this->assertArrayHasKey('description', $result);
        $this->assertArraySubset(['keywords' => []], $result);

        $result = $this->callProtectedMethod('decode', '\RunTracy\Helpers\VendorVersionsPanel', [$lockFile]);
        $this->assertArrayHasKey('_readme', $result);
        $this->assertArraySubset(['packages' => []], $result);

        // fake file with Syntax error
        $path = realpath(__DIR__ . '/../../../tests/');
        $jsonFile = $path . DIRECTORY_SEPARATOR . 'testFake.json';
        $result = $this->callProtectedMethodReturnObj('decode', '\RunTracy\Helpers\VendorVersionsPanel', [$jsonFile]);
        $this->assertNull($result[1]);
        $this->assertStringStartsWith('Syntax error', $result[0]->getError());
    }

    public function testVendorVersionsPanelDecodeWarning()
    {
        // no file
        $path = realpath(__DIR__ . '/../../../tests/');
        $jsonFile = $path . DIRECTORY_SEPARATOR . 'zzzWWW.json';

        $result = $this->callProtectedMethod('decode', '\RunTracy\Helpers\VendorVersionsPanel', [$jsonFile]);
        $this->assertNull($result);
    }
}
