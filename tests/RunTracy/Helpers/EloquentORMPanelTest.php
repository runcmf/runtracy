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
 * Class EloquentORMPanelTest
 * @package Tests\RunTracy\Helpers
 */
class EloquentORMPanelTest extends BaseTestCase
{
    public function testEloquentORMPanel()
    {
        if(class_exists('\\Illuminate\\Database\\Capsule\\Manager')) {
            // Register Eloquent single connections
            $capsule = new \Illuminate\Database\Capsule\Manager;
            $capsule->addConnection($this->cfg['settings']['db']['connections']['mysql']);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            $capsule::connection()->enableQueryLog();

            $this->assertInstanceOf('\Illuminate\Database\Capsule\Manager', $capsule);

            $panel = new \RunTracy\Helpers\EloquentORMPanel(
                $capsule::connection()->getQueryLog()
            );
            $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

            // test Tracy tab
            $this->assertRegexp('#DB Query Info#', $panel->getTab());
            // test Tracy panel
            $this->assertRegexp('#Eloquent ORM#', $panel->getPanel());
        } else {
            $this->markTestSkipped('Illuminate\Database not installed and all tests in this file are invactive!');
        }
    }
}