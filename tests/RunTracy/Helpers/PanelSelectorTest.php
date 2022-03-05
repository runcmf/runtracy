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
 * Class PanelSelectorTest
 * @package Tests\RunTracy\Helpers
 */
class PanelSelectorTest extends BaseTestCase
{
    public function testPanelSelector()
    {
        $panel = new \RunTracy\Helpers\PanelSelector(
            [],
            array_diff_key($this->cfg['settings']['tracy'], ['configs' => null])
        );
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

        // test Tracy tab
        $this->assertRegexp('#Panel Selector#', $panel->getTab());
        // test Tracy panel
        $this->assertRegexp('#Panel Selector#', $panel->getPanel());
    }

    public function testFormatPanelName()
    {
        $words = [
            'showSlimEnvironmentPanel',// 3 words
            'showSlimContainer',// 2 words
            'showXDebugHelper',// 2 upper in word
            'showEloquentORMPanel'// 3 upper in word
        ];
        $result = $this->callProtectedMethod('formatPanelName', '\RunTracy\Helpers\PanelSelector', [$words[0]]);
        $this->assertEquals(' Slim Environment Panel', $result);

        $result = $this->callProtectedMethod('formatPanelName', '\RunTracy\Helpers\PanelSelector', [$words[1]]);
        $this->assertEquals(' Slim Container', $result);

        $result = $this->callProtectedMethod('formatPanelName', '\RunTracy\Helpers\PanelSelector', [$words[2]]);
        $this->assertEquals(' XDebug Helper', $result);

        $result = $this->callProtectedMethod('formatPanelName', '\RunTracy\Helpers\PanelSelector', [$words[3]]);
        $this->assertEquals(' Eloquent ORM Panel', $result);
    }
}
