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
 * Test BaseJsonRpcServer
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

        // fake execute
        $ret = $s->execute();
        $this->assertEquals('2.0', $ret['jsonrpc']);
        $this->assertNull($ret['id']);
        $this->assertEquals(-32700, $ret['error']['code']);
        $this->assertEquals('Parse error', $ret['error']['message']);
        $this->assertNull($ret['error']['data']);
    }

    public function testBaseJsonRpcServerGetServiceMap()
    {
        $s = new BaseJsonRpcServer();
        $this->assertInstanceOf('\RunTracy\Helpers\Console\BaseJsonRpcServer', $s);

        $ret = $this->callProtectedMethod('getServiceMap', '\RunTracy\Helpers\Console\BaseJsonRpcServer', []);
        $this->assertEquals('POST', $ret['transport']);
        $this->assertEquals('JSON-RPC-2.0', $ret['envelope']);
        $this->assertEquals('2.0', $ret['SMDVersion']);
        $this->assertEquals('application/json', $ret['contentType']);
        $this->assertEquals('', $ret['target']);
        $this->assertEquals([], $ret['services']);
        $this->assertEquals('', $ret['description']);
    }

    public function testBaseJsonRpcServerGetDocDescription()
    {
        $s = new BaseJsonRpcServer();
        $this->assertInstanceOf('\RunTracy\Helpers\Console\BaseJsonRpcServer', $s);

        $rc = new \ReflectionClass($s);

        $ret = $this->callProtectedMethod(
            'getDocDescription',
            '\RunTracy\Helpers\Console\BaseJsonRpcServer',
            [$rc->getDocComment(), 'test']
        );
        $this->assertRegexp('/JSON RPC Server/s', $ret);
    }

    public function testBaseJsonRpcServerRegisterInstance()
    {
        $s = new BaseJsonRpcServer();
        $this->assertInstanceOf('\RunTracy\Helpers\Console\BaseJsonRpcServer', $s);

        $s->registerInstance($s, 'Self');
        $ret = $s->registerInstance($s, 'OneMore');
        $this->assertArrayHasKey('Self', $ret->getInstances());
        $this->assertArrayHasKey('OneMore', $ret->getInstances());

        $_GET['smd'] = 'bla-bla';
        $ret = $s->execute();
        $this->assertNotEmpty($ret['services']);
        $this->assertCount(2, $ret['services']);
    }
}
