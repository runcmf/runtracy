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

namespace Tests\RunTracy\Helpers\Console;

use Tests\BaseTestCase;
use RunTracy\Helpers\Console\BaseJsonRpcServer;

/**
 * @runTestsInSeparateProcesses
 * Class BaseJsonRpcServerTest
 * @package Tests\RunTracy\Helpers\Console
 */
class BaseJsonRpcServerTest extends BaseTestCase
{
    public function testBaseJsonRpcServerWithFakeQuery()
    {
        $s = new BaseJsonRpcServer();
        $this->assertInstanceOf('\RunTracy\Helpers\Console\BaseJsonRpcServer', $s);

//        $ret = $this->callProtectedMethod('resetVars', '\RunTracy\Helpers\Console\BaseJsonRpcServer', []);

        // fake execute
        $ret = $s->execute();
        $this->assertEquals('2.0', $ret['jsonrpc']);
        $this->assertTrue($ret['id'] === null);
        $this->assertEquals(-32700, $ret['error']['code']);
        $this->assertEquals('Parse error', $ret['error']['message']);
        $this->assertTrue($ret['error']['data'] === null);

//fwrite(STDERR, '$ret: ' . var_export($ret, true) . " ###\n");
    }
}
