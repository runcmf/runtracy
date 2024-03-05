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
use RunTracy\Helpers\Profiler\Profile;
use RunTracy\Helpers\Profiler\Profiler;
use RunTracy\Helpers\Profiler\ProfilerService;

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
        Profiler::disable();//test disable
        $this->assertFalse(Profiler::isEnabled());
        Profiler::enable();//effective

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
        Profiler::disable();//test disable
        $this->assertFalse(Profiler::isEnabled());
        Profiler::enable(1);//effective

        $panel = new \RunTracy\Helpers\ProfilerPanel($this->cfg['settings']['tracy']['configs']['ProfilerPanel']);
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

        // test Tracy tab
        $this->assertRegexp('#Profiler info#s', $panel->getTab());
        // test Tracy panel
        $this->assertRegexp('#Memory change#s', $panel->getPanel());

        $this->assertRegexp('#absolute#s', $panel->getPanel());
    }

    public function testProfilerService()
    {
        Profiler::enable();

        $p = ProfilerService::getInstance();
        $this->fillTestData($p);
        $this->assertCount(2, $p->getProfiles());

        $panel = new \RunTracy\Helpers\ProfilerPanel($this->cfg['settings']['tracy']['configs']['ProfilerPanel']);
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

        // test Tracy tab
        $this->assertRegexp('#2 profiles#s', $panel->getTab());
        // check svg image exists
        $this->assertRegexp('#svg style=#s', $panel->getPanel());
    }

    protected function fillTestData(&$p)
    {
        $testProfiles =  [
            0 =>
                [
                    'meta' =>
                         [
                            'start_label' => 'initRoutes',
                            'time_offset' => 0,
                            'memory_usage_offset' => 0,
                            'start_time' => 1482687818.8077681,
                            'start_memory_usage' => 1061384,
                            'finish_label' => 'initRoutes',
                            'finish_time' => 1482687818.821182,
                            'finish_memory_usage' => 1075296,
                         ],
                         'absoluteDuration' => 0.013413906097412109,
                         'duration' => 0.013413906097412109,
                         'absoluteMemoryUsageChange' => 13912,
                         'memoryUsageChange' => 13912,
                ],
                1 =>
                [
                    'meta' =>
                         [
                            'start_label' => 'initModules',
                            'time_offset' => 0,
                            'memory_usage_offset' => 0,
                            'start_time' => 1482687818.821209,
                            'start_memory_usage' => 1075696,
                            'finish_label' => 'initModules',
                            'finish_time' => 1482687818.9348431,
                            'finish_memory_usage' => 1490584,
                         ],
                         'absoluteDuration' => 0.11363410949707031,
                         'duration' => 0.11363410949707031,
                         'absoluteMemoryUsageChange' => 414888,
                         'memoryUsageChange' => 414888,
                ],
        ];

        foreach ($testProfiles as $profile) {
            $np = new Profile();
            $np->meta = $profile['meta'];
            $np->absoluteDuration = $profile['absoluteDuration'];
            $np->duration = $profile['duration'];
            $np->absoluteMemoryUsageChange = $profile['absoluteMemoryUsageChange'];
            $np->memoryUsageChange = $profile['memoryUsageChange'];
            $p->addProfile($np);
        }
    }
}
