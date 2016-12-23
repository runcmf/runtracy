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
use RunTracy\Helpers\Profiler\Profiler;

/**
 * @runTestsInSeparateProcesses
 * Class ProfilerPanelTest
 * @package Tests\RunTracy\Helpers
 */
class ProfilerPanelTest extends BaseTestCase
{
    public function testProfilerPanelDisabled()
    {
        $this->assertFalse(Profiler::isEnabled());

        $panel = new \RunTracy\Helpers\ProfilerPanel($this->cfg['settings']['tracy']['configs']['ProfilerPanel']);
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

        // test Tracy tab
        $this->assertRegexp('#Profiler info#', $panel->getTab());
        // test Tracy panel
        $this->assertRegexp('#Profiling is disabled#s', $panel->getPanel());
    }

    public function testProfilerPanelEnabledShowMemEffective()
    {
        $this->assertFalse(Profiler::isEnabled());
        Profiler::enable();//effective
        $this->assertTrue(Profiler::isEnabled());

        $panel = new \RunTracy\Helpers\ProfilerPanel($this->cfg['settings']['tracy']['configs']['ProfilerPanel']);
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

        // test Tracy tab
        $this->assertRegexp('#Profiler info#s', $panel->getTab());
        // test Tracy panel
        $this->assertRegexp('#Memory change#s', $panel->getPanel());

        $this->assertRegexp('#effective#s', $panel->getPanel());

    }

    public function testProfilerPanelEnabledShowMemAbsolute()
    {
        $this->assertFalse(Profiler::isEnabled());
        Profiler::enable(1);//absolute
        $this->assertTrue(Profiler::isEnabled());

        $panel = new \RunTracy\Helpers\ProfilerPanel($this->cfg['settings']['tracy']['configs']['ProfilerPanel']);
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

        // test Tracy tab
        $this->assertRegexp('#Profiler info#s', $panel->getTab());
        // test Tracy panel
        $this->assertRegexp('#Memory change#s', $panel->getPanel());

        $this->assertRegexp('#absolute#s', $panel->getPanel());
    }
}