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

namespace Tests;

use RunTracy\Helpers\Console\WebConsoleRPCServer;

class FakeConsole extends WebConsoleRPCServer
{
    public function setVar($var, $value, $index = '')
    {
        if (!empty($index)) {
            $this->$var = [$index => $value];
        } else {
            $this->$var = $value;
        }
    }

    public function authUser($u, $p)
    {
        return $this->authenticateUser($u, $p);
    }

    public function authToken($t)
    {
        return $this->authenticateToken($t);
    }

    public function getHomeDir($user)
    {
        return $this->getHomeDirectory($user);
    }

    public function getEnv()
    {
        return $this->getEnvironment();
    }

    public function setEnv($env)
    {
        return $this->setEnvironment($env);
    }

    public function init($token, $environment)
    {
        return $this->initialize($token, $environment);
    }
}
