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

namespace RunTracy\Controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use RunTracy\Helpers\Console\WebConsoleRPCServer;
use Interop\Container\ContainerInterface;

class RunTracyConsole extends WebConsoleRPCServer
{
    private $ci;

    public function __construct(ContainerInterface $ci)
    {
        parent::__construct();

        $this->ci = $ci;
    }

    public function index(Request $request, Response $response)
    {
        $cfg = $this->ci->get('settings')['tracy']['configs'];

        $this->noLogin = $cfg['ConsoleNoLogin'] ?: false;
        foreach ($cfg['ConsoleAccounts'] as $u => $p) {
            $this->accounts[$u] = $p;
        }
        $this->passwordHashAlgorithm = $cfg['ConsoleHashAlgorithm'] ?: '';
        $this->homeDirectory = $cfg['ConsoleHomeDirectory'] ?: '';

        return $response->withJson($this->execute());
    }
}
