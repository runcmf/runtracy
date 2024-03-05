<?php

/**
 * Copyright 2017 1f7.wizard@gmail.com
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
use RunTracy\Collectors\IdormCollector;

/**
 * @runTestsInSeparateProcesses
 * Class IdiormPanelTest
 * @package Tests\RunTracy\Helpers
 */
class IdiormPanelTest extends BaseTestCase
{
    public function testIdiormPanel()
    {
        if (!class_exists('\ORM')) {
            $this->markTestSkipped('Idiorm not installed and all tests in this file are invactive!');
        } else {
            $this->initIdiorm();
            $panel = new \RunTracy\Helpers\IdiormPanel();
            $this->assertInstanceOf('\Tracy\IBarPanel', $panel);
            $this->assertInstanceOf('\RunTracy\Helpers\IdiormPanel', $panel);

            // test Tracy tab
            $this->assertRegexp('#Idiorm query logs#', $panel->getTab());
            // test Tracy panel
            $this->assertRegexp('#Slim 3 / Idiorm#', $panel->getPanel());
        }
    }

    public function testIdiormPanelParser()
    {
        // empty data
        $result = $this->callProtectedMethod('parse', '\RunTracy\Helpers\IdiormPanel', []);
        $this->assertRegexp('#No Logs#', $result);

        // some as in collector test
        $collector = new IdormCollector();
        $this->assertInstanceOf('\RunTracy\Collectors\IdormCollector', $collector);

        IdormCollector::setLog($this->getFakeData());

        $logs = IdormCollector::getLog();
        $this->assertEquals(3, count($logs));

        // now with data
        $result = $this->callProtectedMethod('parse', '\RunTracy\Helpers\IdiormPanel', []);
        $this->assertRegexp('#0.00042415#', $result);// number_format len = 8
        $this->assertRegexp('#0.00013685#', $result);// number_format len = 8
        $this->assertRegexp('#0.00020099#', $result);// number_format len = 8
    }

    protected function getFakeData()
    {
        return [
            0 => [
                'time' => 0.00042414665222168,
                'query' => "SELECT `u`.*, `g`.*, `o`.`logged`, `o`.`idle` FROM `users` `u` 
                INNER JOIN `groups` `g` ON `u`.`group_id` = `g`.`g_id` 
                LEFT JOIN online `o` ON o.user_id='u.id' WHERE `u`.`id` = '2'"
            ],
            1 => [
                'time' => 0.0001368522644043,
                'query' => "REPLACE INTO online (user_id, ident, logged) VALUES('2', 'admin', '1485932309')"
            ],
            2 => [
                'time' => 0.00020098686218262,
                'query' => "SELECT `user_id`, `ident`, `logged`, `idle` FROM `online` WHERE `logged` < '1485932009'"
            ]
        ];
    }

    public function initIdiorm()
    {
        // mysql
        $cfg = $this->cfg['settings']['db']['connections']['mysql'];
        $cfg['db_type'] = 'mysql';
        switch ($cfg['db_type']) {
            case 'mysql':
                \ORM::configure('mysql:host=' . $cfg['host'] . ';dbname=' . $cfg['database']);
                \ORM::configure('driver_options', [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
                break;
            case 'sqlite':
            case 'sqlite3':
                \ORM::configure('sqlite:./' . $cfg['database']);
                break;
            case 'pgsql':
                \ORM::configure('pgsql:host=' . $cfg['_host'] . 'dbname=' . $cfg['database']);
                break;
        }
        \ORM::configure('username', $cfg['username']);
        \ORM::configure('password', $cfg['password']);

        return $this;
    }
}
