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

namespace RunTracy\Collectors;

use Slim\Container;
use Doctrine\DBAL\Logging\DebugStack;

/**
 * Class DoctrineCollector
 * @package RunTracy\Collectors
 */
class DoctrineCollector
{
    /**
     * DoctrineCollector constructor.
     * @param Container|null $c
     * @param string $containerName
     * @throws \Exception
     */
    public function __construct(Container $c = null, $containerName = '')
    {
        if ($c === null || !$c->has($containerName)) {
            return 0;
        }
        $dm = $c->get($containerName);

        // check instance
        switch (true) {
            case $dm instanceof \Doctrine\DBAL\Query\QueryBuilder:
                $conf = $dm->getConnection()->getConfiguration();
                break;
            case $dm instanceof \Doctrine\DBAL\Connection:
                $conf = $dm->getConfiguration();
                break;
            case $dm instanceof \Doctrine\ORM\EntityManager:
                $conf = $dm->getConnection()->getConfiguration();
                break;
            case $dm instanceof \Doctrine\ORM\QueryBuilder:
                $conf = $dm->getEntityManager()->getConnection()->getConfiguration();
                break;
            default:
                throw new \Exception('Neither Doctrine DBAL neither ORM not found');
                break;
        }

        $conf->setSQLLogger(new DebugStack());
        $c['doctrineConfig'] = $conf;

        return true;
    }
}
