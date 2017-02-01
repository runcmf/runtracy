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

namespace Tests\RunTracy\Controllers;

use Tests\BaseTestCase;
use RunTracy\Collectors\IdormCollector;

/**
 * @runTestsInSeparateProcesses
 * Class IdiormCollectorTest
 * @package Tests\RunTracy\Controllers
 */
class IdiormCollectorTest extends BaseTestCase
{
    public function testIdiormCollector()
    {
        $collector = new IdormCollector();

        $this->assertInstanceOf('\RunTracy\Collectors\IdormCollector', $collector);

        // empty without DB
        $this->assertEmpty(IdormCollector::getLog());

        // fill fake data
        IdormCollector::setLog($this->getFakeData());

        $logs = IdormCollector::getLog();
        $this->assertEquals(3, count($logs));
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
}
