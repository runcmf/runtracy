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
 * Class TwigPanelTest
 * @package Tests\RunTracy\Helpers
 */
class TwigPanelTest extends BaseTestCase
{
    public function testTwigPanel()
    {
        if (class_exists('\\Slim\\Views\\TwigExtension')) {
            $app = new \Slim\App($this->cfg);
            $c = $app->getContainer();

            $c['twig_profile'] = function () {
                if (class_exists('\Twig_Profiler_Profile', false)) {
                    $profile = new \Twig_Profiler_Profile();
                } else {
                    $profile = new \Twig\Profiler\Profile();
                }
                return $profile;
            };

            $view = new \Slim\Views\Twig(
                $this->cfg['settings']['view']['template_path'],
                $this->cfg['settings']['view']['twig']
            );
            // Add extensions
            $view->addExtension(new \Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));

            if (class_exists('\Twig_Extension_Profiler', false)) {
                $profileExtention = new \Twig_Extension_Profiler($c['twig_profile']);
            } else {
                $profileExtention = new \Twig\Extension\ProfilerExtension($c['twig_profile']);
            }
            $view->addExtension($profileExtention);

            if (class_exists('\Twig_Extension_Debug', false)) {
                $debugExtention = new \Twig_Extension_Debug();
            } else {
                $debugExtention = new \Twig\Extension\DebugExtension();
            }
            $view->addExtension($debugExtention);

            $this->assertInstanceOf('\Slim\App', $app);
            $this->assertInstanceOf('\Slim\Container', $c);
            $this->assertInstanceOf('\Slim\Views\Twig', $view);

            if (class_exists('\Twig_Profiler_Profile', false)) {
                $this->assertInstanceOf('\Twig_Profiler_Profile', $c['twig_profile']);
            } else {
                $this->assertInstanceOf('\Twig\Extension\ProfilerExtension', $c['twig_profile']);
            }
            
            $panel = new \RunTracy\Helpers\TwigPanel($c['twig_profile']);

            $this->assertInstanceOf('\Tracy\IBarPanel', $panel);

            // test Tracy tab
            $this->assertRegexp('#Twig Info#', $panel->getTab());
            // test Tracy panel
            $this->assertRegexp('#Twig profiler result#', $panel->getPanel());
        } else {
            $this->markTestSkipped('Twig/TwigExtension not installed and all tests in this file are invactive!');
        }
    }
}
