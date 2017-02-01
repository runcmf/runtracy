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

class IdormCollector
{
    private static $qLog;

    public function __construct()
    {
        self::$qLog = [];
        // enable logging
        \ORM::configure('logging', true);
        // Collect query info
        \ORM::configure('logger', function ($query, $time) {
            self::$qLog[] = [
                'time' => $time,
                'query' => $query
            ];
        });
    }

    public static function getLog()
    {
        return self::$qLog;
    }

    public static function setLog(array $data = [])
    {
        self::$qLog = array_merge(self::$qLog, $data);
    }
}
