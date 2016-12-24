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
use Tests\FakeConsole;

/**
 * @runTestsInSeparateProcesses
 * Class WebConsoleRPCServerTest
 * @package Tests\RunTracy\Helpers\Console
 */
class WebConsoleRPCServerTest extends BaseTestCase
{
    public function testWebConsoleRPCServerUtilities()
    {
        $s = new \RunTracy\Helpers\Console\WebConsoleRPCServer();
        $this->assertInstanceOf('\RunTracy\Helpers\Console\BaseJsonRpcServer', $s);

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

    public function testWebConsoleRPCServerAuth()
    {
        $cfg = $this->cfg['settings']['tracy']['configs'];

        $console = new FakeConsole;
        $this->assertInstanceOf('\RunTracy\Helpers\Console\BaseJsonRpcServer', $console);

        $console->setVar('no_login', ($cfg['ConsoleNoLogin'] ?: false));
        foreach ($cfg['ConsoleAccounts'] as $u => $p) {
            $console->setVar('accounts', $p, $u);
        }
        $console->setVar('password_hash_algorithm', ($cfg['ConsoleHashAlgorithm'] ?: ''));
        $console->setVar('home_directory', ($cfg['ConsoleHomeDirectory'] ?: ''));

        // normal user
        $ret = $console->authUser('dev', 'dev');
        $this->assertEquals('dev:358e86472ec7619731baf6950db699b26e2c2df8d51a31159d40ae4987f6fbab', $ret);
        // get user name
        $user = $console->authToken($ret);
        $this->assertEquals('dev', $user);

        // test without login
        $console->setVar('no_login', true);
        $user = $console->authToken($ret);
        $this->assertTrue($user);

        // fake token
        $console->setVar('no_login', false);
        $this->expectException(\RunTracy\Exceptions\IncorrectUserOrPassword::class);
        $console->authToken('dev:358e8');
    }

    public function testWebConsoleRPCServerAuthException()
    {
        $cfg = $this->cfg['settings']['tracy']['configs'];

        $console = new FakeConsole;
        $this->assertInstanceOf('\RunTracy\Helpers\Console\BaseJsonRpcServer', $console);

        $console->setVar('no_login', ($cfg['ConsoleNoLogin'] ?: false));
        foreach ($cfg['ConsoleAccounts'] as $u => $p) {
            $console->setVar('accounts', $p, $u);
        }
        $console->setVar('password_hash_algorithm', ($cfg['ConsoleHashAlgorithm'] ?: ''));
        $console->setVar('home_directory', ($cfg['ConsoleHomeDirectory'] ?: ''));

        // fake password
        $this->expectException(\RunTracy\Exceptions\IncorrectUserOrPassword::class);
        $console->authUser('dev', 'zzz');
    }

    public function testWebConsoleRPCServerCommon()
    {
        $cfg = $this->cfg['settings']['tracy']['configs'];

        $console = new FakeConsole;
        $this->assertInstanceOf('\RunTracy\Helpers\Console\BaseJsonRpcServer', $console);

        $console->setVar('no_login', ($cfg['ConsoleNoLogin'] ?: false));
        foreach ($cfg['ConsoleAccounts'] as $u => $p) {
            $console->setVar('accounts', $p, $u);
        }
        $console->setVar('password_hash_algorithm', ($cfg['ConsoleHashAlgorithm'] ?: ''));
        $console->setVar('home_directory', ($cfg['ConsoleHomeDirectory'] ?: ''));

        $ret = $console->getHomeDir('dev');
        $this->assertRegexp('#\/runcmf\/#s', $ret);

        // check if (is_string($this->home_directory))
        $console->setVar('home_directory', 123);
        $ret = $console->getHomeDir('dev');
        $this->assertRegexp('#\/runcmf\/#s', $ret);

        // return to def
        $console->setVar('home_directory', ($cfg['ConsoleHomeDirectory'] ?: ''));

        $ret = $console->getEnv();
        $this->assertArrayHasKey('path', $ret);
        $this->assertArrayHasKey('hostname', $ret);

        // set fake dir
        $ret = $console->setEnv([
            'path' => '/no-such-dir',
            'hostname' => 'localhost'
        ]);
        $this->assertRegexp('/Current working directory not found/s', $ret['output']);

        // set real dir
        $env = [
            'path' => getcwd(),
            'hostname' => 'localhost'
        ];
        $ret = $console->setEnv($env);
        $this->assertFalse($ret);// false - all ok

        // normal user, get token
        $ret = $console->authUser('dev', 'dev');
        $this->assertEquals('dev:358e86472ec7619731baf6950db699b26e2c2df8d51a31159d40ae4987f6fbab', $ret);

        //init($token, $environment)
        $ret = $console->init($ret, $env);
        $this->assertFalse($ret);// false - all ok

        $ret = $console->login('dev', 'dev');
        $this->assertArrayHasKey('token', $ret);
        $this->assertArrayHasKey('environment', $ret);
        // runcmf/
        $this->assertRegexp('#runcmf\/#s', $ret['environment']['path']);

        // chdir to one level up
        $cd = $console->cd($ret['token'], $ret['environment'], '../');
        // check path not contain 'runcmf'
        $this->assertTrue(strpos($cd['environment']['path'], 'runcmf') === false);
        // here 'vendor' must be in path
        // travis path /home/travis/build/runcmf/runtracy/
//        $this->assertFalse(strpos($cd['environment']['path'], 'vendor') === false);

        // check completion
        $comp = $console->completion($ret['token'], $ret['environment'], 'run');
        // $comp containt dirs list
        $this->assertArrayHasKey('completion', $comp);
        $this->assertTrue(in_array('runtracy', $comp['completion']));
    }

    public function testWebConsoleRPCServerRun()
    {
        $cfg = $this->cfg['settings']['tracy']['configs'];

        $console = new FakeConsole;
        $this->assertInstanceOf('\RunTracy\Helpers\Console\BaseJsonRpcServer', $console);

        $console->setVar('no_login', ($cfg['ConsoleNoLogin'] ?: false));
        foreach ($cfg['ConsoleAccounts'] as $u => $p) {
            $console->setVar('accounts', $p, $u);
        }
        $console->setVar('password_hash_algorithm', ($cfg['ConsoleHashAlgorithm'] ?: ''));
        $console->setVar('home_directory', ($cfg['ConsoleHomeDirectory'] ?: ''));

        // login
        $ret = $console->login('dev', 'dev');
        $this->assertArrayHasKey('token', $ret);
        $this->assertArrayHasKey('environment', $ret);

        $res = $console->run($ret['token'], $ret['environment'], 'run');
        // 'output' => 'sh: 1: run: not found'
        $this->assertArrayHasKey('output', $res);
        $this->assertRegexp('#run: not found#s', $res['output']);

        // test sh command
        $res = $console->run(
            $ret['token'],
            $ret['environment'],
            'if ls / > /dev/null 2> /dev/null ;then echo ok; else echo not_ok; fi'
        );
        $this->assertEquals('ok', $res['output']);

        $res = $console->run(
            $ret['token'],
            $ret['environment'],
            'if ls /no-such-dir > /dev/null 2> /dev/null ;then echo ok; else echo not_ok; fi'
        );
        $this->assertEquals('not_ok', $res['output']);

        // test php command
        $res = $console->run($ret['token'], $ret['environment'], 'php -r \'echo sha1("dev");\'');
        $this->assertEquals('34c6fceca75e456f25e7e99531e2425c6c1de443', $res['output']);
    }
}
