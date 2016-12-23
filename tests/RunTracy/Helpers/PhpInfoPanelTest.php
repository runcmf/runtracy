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

namespace Tests\RunTracy\Helpers;

use Tests\BaseTestCase;

/**
 * @runTestsInSeparateProcesses
 * Class PhpInfoPanelTest
 * @package Tests\RunTracy\Helpers
 */
class PhpInfoPanelTest extends BaseTestCase
{
    public function testPhpInfoPanel()
    {
        $panel = new \RunTracy\Helpers\PhpInfoPanel();
        $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

        // test Tracy tab
        $this->assertRegexp('/PHP Info/s', $panel->getTab());
        // test Tracy panel
        $this->assertRegexp('/phpinfo/s', $panel->getPanel());
    }

    /**
     * @covers \RunTracy\Helpers\PhpInfoPanel::removeElementsByTagName()
     */
    public function testRemoveElementsByTagName()
    {
//        ob_start();
//        phpinfo();
//        $phpInfo = ob_get_contents();
//        ob_get_clean();
        // FIXME buffer not work in phpunit ????
        $html = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <style type="text/css">
                body {background-color: #fff; color: #222; font-family: sans-serif;}
            </style>
            <title>phpinfo()</title>
            <meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
        </head>
        <body>
            <div class="center">
                <table>
                    <tr class="h">
                        <td>
                            <a href="http://www.php.net/">
                                <img border="0" src="here logo base64" alt="PHP logo" />
                            </a>
                            <h1 class="p">PHP Version 7.0.13-1+deb.sury.org~trusty+1</h1>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr class="v">
                        <td>
                            <p>This program is free software;</p>
                            <p>This program is distributed</p>
                            <p>If you did not receive a copy of the PHP license</p>
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>';

        // warning: DOMDocument::loadHTML(): htmlParseEntityRef: no name in Entity, line: 64
        // suppress warnings
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $body = $dom->getElementsByTagName('body')->item(0);

        // check img tag exists
        $phpInfo = $dom->saveHTML($body);
        $this->assertTrue((boolean)preg_match('/<img[^>]*/si', $phpInfo));

        // remove img tag
        $this->callProtectedMethod('removeElementsByTagName', '\RunTracy\Helpers\PhpInfoPanel', ['img', $body]);

        // check img tag not exists
        $phpInfo = $dom->saveHTML($body);
        $this->assertFalse((boolean)preg_match('/<img[^>]*/si', $phpInfo));
    }
}
