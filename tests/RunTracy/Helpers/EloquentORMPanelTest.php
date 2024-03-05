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
        if (!class_exists('\\Illuminate\\Database\\Capsule\\Manager')) {
            $this->markTestSkipped('Illuminate\Database not installed and all tests in this file are invactive!');
        } else {
            // Register Eloquent single connections
            $capsule = new \Illuminate\Database\Capsule\Manager();
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
        }
    }

    public function testEloquentORMPanelParser()
    {
        $data =
            [
                'query' => 'update `mybb_sessions` set `uid` = ?, `time` = ?, `location` = ?,
                    `useragent` = ?, `location1` = ?, `location2` = ?, `nopermission` = ? where `sid` = ?',
                'bindings' =>
                    [
                        0 => 1,
                        1 => 1482512713,
                        2 => '/?',
                        3 => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:50.0) Gecko/20100101 Firefox/50.0',
                        4 => 0,
                        5 => 0,
                        6 => 0,
                        7 => '89a412a4ad6ad9df88b42ca4c12bb271',
                    ],
                    'time' => 4.9100000000000001,
            ];
        // getHeader()
        $headerRow = $this->callProtectedMethod('getHeader', '\RunTracy\Helpers\EloquentORMPanel', []);
        // <thead><tr><th><b>Count</b></th><th><b>Time,&nbsp;ms</b></th><th>Query / Bindings</th></tr></thead>
        $this->assertRegexp('#<thead><tr><th>#', $headerRow);

        // getBaseRow()
        $baseRow = $this->callProtectedMethod('getBaseRow', '\RunTracy\Helpers\EloquentORMPanel', []);
        // <tr><td>%s</td><td>%s</td><td>%s</td></tr>
        $this->assertRegexp('#<tr><td>#', $baseRow);

        // parse($data)
        $result = $this->callProtectedMethod('parse', '\RunTracy\Helpers\EloquentORMPanel', [[0 => $data]]);
        $this->assertRegexp('#89a412a4ad6ad9df88b42ca4c12bb271#', $result);
    }
}
