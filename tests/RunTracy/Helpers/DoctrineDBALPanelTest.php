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

/**
 * @runTestsInSeparateProcesses
 * Class DoctrineDBALTanelTest
 * @package Tests\RunTracy\Helpers
 */
class DoctrineDBALPanelTest extends BaseTestCase
{
    public function testDoctrineDBALPanel()
    {
        if (!class_exists('\Doctrine\DBAL\Configuration')) {
            $this->markTestSkipped('Doctrine DBAL not installed and all tests in this file are invactive!');
        } else {
            $logger = $this->dbalLogger();
            $this->assertInstanceOf('\Doctrine\DBAL\Configuration', $logger);

            $dbal = $this->initDBAL();
            $this->assertInstanceOf('\Doctrine\DBAL\Query\QueryBuilder', $dbal);

            $panel = new \RunTracy\Helpers\DoctrineDBALPanel(
                $logger
            );
            $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

            // test Tracy tab
            $this->assertRegexp('#Doctrine DBAL Panel#', $panel->getTab());
            // test Tracy panel
            $this->assertRegexp('#Slim 3 / Doctrine DBAL#', $panel->getPanel());
        }
    }

    public function testDoctrineDBALPanelParser()
    {

        $data = [
            1 => [
                'sql' => 'SELECT id, name FROM authors WHERE id = :id',
                'params' => [
                    ':id' => 22,
                ],
                'types' => [
                    ':id' => 'integer',
                ],
                'executionMS' => 0.010435104370117,
            ],
            2 => [
            'sql' => 'SELECT author_id, title, isbn FROM authors, books WHERE (author_id = :id) AND (title = :title)',
                'params' => [
                    ':id' => 3,
                    ':title' => 'Dragondrums'
                ],
                'types' => [
                    ':id' => 'integer',
                    ':title' => 'string'
                ],
                'executionMS' => 0.0032620429992676,
            ]
        ];


        // formatArrayData($data)
        $result = $this->callProtectedMethod('formatArrayData', '\RunTracy\Helpers\DoctrineDBALPanel', [
            $data[1]['params']
        ]);
        $this->assertRegexp('#":id": 22#', $result);
        $result = $this->callProtectedMethod('formatArrayData', '\RunTracy\Helpers\DoctrineDBALPanel', [
            $data[2]['params']
        ]);
        // one param
        $this->assertRegexp('#":id": 3#', $result);
        $this->assertRegexp('#":title": "Dragondrums"#', $result);
        // all together
        $this->assertRegexp('#":id": 3,\n":title": "Dragondrums"#', $result);

        // transformNumericType($data)
        // prepare array for transformNumericType
        $result1 = $this->callProtectedMethod('formatArrayData', '\RunTracy\Helpers\DoctrineDBALPanel', [
            $data[1]['types']
        ]);
        $this->assertRegexp('#":id": "integer"#', $result1);
        // then transformNumericType
        $result2 = $this->callProtectedMethod('transformNumericType', '\RunTracy\Helpers\DoctrineDBALPanel', [
            $result1
        ]);
        // some
        $this->assertRegexp('#":id": "integer"#', $result2);

        // some twice
        $result3 = $this->callProtectedMethod('formatArrayData', '\RunTracy\Helpers\DoctrineDBALPanel', [
            $data[2]['types']
        ]);
        // one param
        $this->assertRegexp('#":id": "integer"#', $result3);
        $this->assertRegexp('#":title": "string"#', $result3);
        // all together
        $this->assertRegexp('#":id": "integer",\n":title": "string"#', $result3);
        // then transformNumericType
        $result4 = $this->callProtectedMethod('transformNumericType', '\RunTracy\Helpers\DoctrineDBALPanel', [
            $result3
        ]);
        // some
        $this->assertRegexp('#":id": "integer"#', $result4);
        $this->assertRegexp('#":title": "string"#', $result4);
        $this->assertRegexp('#":id": "integer",\n":title": "string"#', $result4);

        // parse($data)
        $result = $this->callProtectedMethod('parse', '\RunTracy\Helpers\DoctrineDBALPanel', [$data]);
        $this->assertRegexp('#FROM authors WHERE#', $result);
        $this->assertRegexp('#0.01043510#', $result);
        $this->assertRegexp('#FROM authors, books WHERE#', $result);
        $this->assertRegexp('#Dragondrums#', $result);
        $this->assertRegexp('#0.00326204#', $result);
    }

    public function dbalLogger()
    {
        $config = new \Doctrine\DBAL\Configuration;
        $config->setSQLLogger(new \Doctrine\DBAL\Logging\DebugStack());
        return $config;
    }

    public function initDBAL()
    {
        $conn = \Doctrine\DBAL\DriverManager::getConnection(
            [
                'driver' => 'pdo_mysql',
                'host' => '127.0.0.1',
                'user' => 'dbuser',
                'password' => '123',
                'dbname' => 'bookshelf',
                'port' => 3306,
                'charset' => 'utf8',
            ],
            $this->dbalLogger()
        );
        return $conn->createQueryBuilder();
    }
}
