<?php

declare(strict_types=1);

/**
 * Copyright 2017 1f7.wizard@gmail.com.
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

namespace RunTracy\Collectors;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;
use Exception;
use Psr\Container\ContainerInterface as Container;

/**
 * Class DoctrineCollector.
 */
class DoctrineCollector
{
    /**
     * DoctrineCollector constructor.
     *
     * @param null|Container $c
     * @param string         $containerName
     *
     * @throws Exception
     */
    public function __construct(Container $c = null, string $containerName = '')
    {
        if ($c === null || !$c->has($containerName)) {
            return 0;
        }
        $dm = $c->get($containerName);

        // check instance
        switch (true) {
            case $dm instanceof Connection:
                $conf = $dm->getConfiguration();

                break;

            case $dm instanceof EntityManager:
            case $dm instanceof QueryBuilder:
                $conf = $dm->getConnection()->getConfiguration();

                break;

            case $dm instanceof OrmQueryBuilder:
                $conf = $dm->getEntityManager()->getConnection()->getConfiguration();

                break;

            default:
                throw new Exception('Neither Doctrine DBAL neither ORM not found');
        }

        $conf->setSQLLogger(new DebugStack());

        if (method_exists($c, 'set')) {
            $c->set('doctrineConfig', $conf);
        } else {
            $c['doctrineConfig'] = $conf;
        }

        return true;
    }
}
