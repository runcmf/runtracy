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
use RunTracy\Collectors\DoctrineCollector;

/**
 * @runTestsInSeparateProcesses
 * Class DoctrineCollectorTest
 * @package Tests\RunTracy\Controllers
 */
class DoctrineCollectorTest extends BaseTestCase
{
    public function testDoctrineCollector()
    {
        $app = new \Slim\App();
        $c = $app->getContainer();

        $collector = new DoctrineCollector();
        $this->assertInstanceOf('\RunTracy\Collectors\DoctrineCollector', $collector);
        $this->isFalse($collector);

        // with container and fake doctrine container name. also false
        $collector = new DoctrineCollector($c, 'fake');
        $this->assertInstanceOf('\RunTracy\Collectors\DoctrineCollector', $collector);
        $this->isFalse($collector);
    }

    public function testDoctrineCollectorDBALreturnQueryBuilder()
    {
        $app = new \Slim\App();
        $c = $app->getContainer();

        $c['dbal'] = function () {
            $conn = \Doctrine\DBAL\DriverManager::getConnection(
                $this->getDoctrineDBALConfig(),
                new \Doctrine\DBAL\Configuration()
            );
            // return DBAL\Query\QueryBuilder
            return $conn->createQueryBuilder();
        };
        $this->assertInstanceOf('\Doctrine\DBAL\Query\QueryBuilder', $c->get('dbal'));

        // also false
        $collector = new DoctrineCollector($c, 'fake');
        $this->assertInstanceOf('\RunTracy\Collectors\DoctrineCollector', $collector);
        $this->isFalse($collector);

        // must true
        $collector = new DoctrineCollector($c, 'dbal');
        $this->assertInstanceOf('\RunTracy\Collectors\DoctrineCollector', $collector);
        $this->isTrue($collector);
        $this->assertInstanceOf('\Doctrine\DBAL\Configuration', $c['doctrineConfig']);
    }

    public function testDoctrineCollectorDBALreturnConnection()
    {
        $app = new \Slim\App();
        $c = $app->getContainer();

        $c['dbal'] = function () {
            $conn = \Doctrine\DBAL\DriverManager::getConnection(
                $this->getDoctrineDBALConfig(),
                new \Doctrine\DBAL\Configuration()
            );
            // return DBAL\Connection
            return $conn;
        };
        $this->assertInstanceOf('\Doctrine\DBAL\Connection', $c->get('dbal'));

        // must true
        $collector = new DoctrineCollector($c, 'dbal');
        $this->assertInstanceOf('\RunTracy\Collectors\DoctrineCollector', $collector);
        $this->isTrue($collector);
        $this->assertInstanceOf('\Doctrine\DBAL\Configuration', $c['doctrineConfig']);
    }

    public function testDoctrineCollectorORMreturnEntityManager()
    {
        $cfg = require __DIR__ . '/../../Settings.php';
        $app = new \Slim\App($cfg);
        $c = $app->getContainer();

        // return EntityManager
        $c['em'] = function ($c) {
            $settings = $c->get('settings');
            $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
                $settings['doctrine']['meta']['entity_path'],
                $settings['doctrine']['meta']['auto_generate_proxies'],
                $settings['doctrine']['meta']['proxy_dir'],
                $settings['doctrine']['meta']['cache'],
                false
            );
            return \Doctrine\ORM\EntityManager::create($settings['doctrine']['connection'], $config);
        };
        $this->assertInstanceOf('\Doctrine\ORM\EntityManager', $c->get('em'));

        // must true
        $collector = new DoctrineCollector($c, 'em');
        $this->assertInstanceOf('\RunTracy\Collectors\DoctrineCollector', $collector);
        $this->isTrue($collector);
        $this->assertInstanceOf('\Doctrine\DBAL\Configuration', $c['doctrineConfig']);
    }

    public function testDoctrineCollectorORMreturnQueryBuilder()
    {
        $cfg = require __DIR__ . '/../../Settings.php';
        $app = new \Slim\App($cfg);
        $c = $app->getContainer();

        // return ORM QueryBuilder
        $c['em'] = function ($c) {
            $settings = $c->get('settings');
            $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
                $settings['doctrine']['meta']['entity_path'],
                $settings['doctrine']['meta']['auto_generate_proxies'],
                $settings['doctrine']['meta']['proxy_dir'],
                $settings['doctrine']['meta']['cache'],
                false
            );
            $em = \Doctrine\ORM\EntityManager::create($settings['doctrine']['connection'], $config);
            return $em->createQueryBuilder();
        };
        $this->assertInstanceOf('\Doctrine\ORM\QueryBuilder', $c->get('em'));

        // must true
        $collector = new DoctrineCollector($c, 'em');
        $this->assertInstanceOf('\RunTracy\Collectors\DoctrineCollector', $collector);
        $this->isTrue($collector);
        $this->assertInstanceOf('\Doctrine\DBAL\Configuration', $c['doctrineConfig']);
    }

    public function testDoctrineCollectorException()
    {
        $cfg = require __DIR__ . '/../../Settings.php';
        // Instantiate the application
        $app = new \Slim\App($cfg);
        $c = $app->getContainer();

        // no return. expect exeption 'Neither Doctrine DBAL neither ORM not found'
        $c['em'] = function ($c) {
            $settings = $c->get('settings');
            $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
                $settings['doctrine']['meta']['entity_path'],
                $settings['doctrine']['meta']['auto_generate_proxies'],
                $settings['doctrine']['meta']['proxy_dir'],
                $settings['doctrine']['meta']['cache'],
                false
            );
            $em = \Doctrine\ORM\EntityManager::create($settings['doctrine']['connection'], $config);
            $em->createQueryBuilder();
        };

        $this->expectExceptionMessage('Neither Doctrine DBAL neither ORM not found');
        $collector = new DoctrineCollector($c, 'em');
    }

    protected function getDoctrineDBALConfig()
    {
        return [
            'driver' => 'pdo_mysql',
            'host' => '127.0.0.1',
            'user' => 'dbuser',
            'password' => '123',
            'dbname' => 'bookshelf',
            'port' => 3306,
            'charset' => 'utf8',
        ];
    }
}
