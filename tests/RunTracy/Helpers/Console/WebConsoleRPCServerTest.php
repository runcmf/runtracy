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

/**
 * @runTestsInSeparateProcesses
 * Class WebConsoleRPCServerTest
 * @package Tests\RunTracy\Helpers\Console
 */
class WebConsoleRPCServerTest extends BaseTestCase
{
    public function testWebConsoleRPCServer()
    {
        $s = new \RunTracy\Helpers\Console\WebConsoleRPCServer();
        $this->assertInstanceOf('\RunTracy\Helpers\Console\BaseJsonRpcServer', $s);

        // Utilities

        // isEmptyString($string)
        $retVal = $this->callProtectedMethod('isEmptyString', '\RunTracy\Helpers\Console\WebConsoleRPCServer', ['']);
        $this->assertTrue($retVal);
        $retVal = $this->callProtectedMethod('isEmptyString', '\RunTracy\Helpers\Console\WebConsoleRPCServer', ['ZZ']);
        $this->assertFalse($retVal);

        // isEqualStrings($string1, $string2)
        $vars = [0 => '123', 1 => '123'];
        $retVal = $this->callProtectedMethod('isEqualStrings', '\RunTracy\Helpers\Console\WebConsoleRPCServer', $vars);
        $this->assertTrue($retVal);
        $vars = [0 => '123', 1 => '333'];
        $retVal = $this->callProtectedMethod('isEqualStrings', '\RunTracy\Helpers\Console\WebConsoleRPCServer', $vars);
        $this->assertFalse($retVal);

        // getHash($algorithm, $string)
        $vars = [0 => 'sha1', 1 => 'dev'];
        $retVal = $this->callProtectedMethod('getHash', '\RunTracy\Helpers\Console\WebConsoleRPCServer', $vars);
        $this->assertEquals('34c6fceca75e456f25e7e99531e2425c6c1de443', $retVal);

        $vars = [0 => 'md5', 1 => 'dev'];
        $retVal = $this->callProtectedMethod('getHash', '\RunTracy\Helpers\Console\WebConsoleRPCServer', $vars);
        $this->assertEquals('e77989ed21758e78331b20e477fc5582', $retVal);
    }
}
