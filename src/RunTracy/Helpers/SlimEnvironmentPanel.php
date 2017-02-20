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


namespace RunTracy\Helpers;

use Tracy\IBarPanel;

class SlimEnvironmentPanel implements IBarPanel
{
    private $content;
    private $ver;
    private $icon;

    public function __construct($data = null, array $ver = [])
    {
        $this->content = $data;
        $this->ver = $ver;
    }

    public function getTab()
    {
        $this->icon = '<svg enable-background="new 0 0 32 32" height="32px" version="1.1" viewBox="0 0 32 32" '.
            'width="32px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"><path fill="#239626" d="M29.7,'.
            '2.767C29.593,2.317,29.189,2,28.728,2c-0.002,0-0.004,0-0.006,0c-5.267,0.033-18.075,0.916-23.246,8.38  '.
            'c-2.686,3.878-2.917,8.913-0.687,14.965c0.017,0.047,0.052,0.079,0.075,0.122C4.067,27.44,3.758,28.753,'.
            '3.75,28.79 c-0.116,0.54,0.229,1.072,0.769,1.188C4.589,29.993,4.659,30,4.729,30c0.461,0,0.876-0.321,'.
            '0.977-0.79 c0.021-0.102,2.569-10.373,11.577-16.378c0.46-0.306,0.584-0.927,0.277-1.387s-0.927-0.585-'.
            '1.387-0.277 c-5.108,3.405-8.254,8.008-10.104,11.656c-1.324-4.578-0.977-8.377,1.052-11.305c3.993-5.'.
            '764,13.464-7.154,19.181-7.448  C25.072,5.872,23.728,9.174,23.728,15c0,3.12-0.885,5.522-2.629,7.14c-'.
            '2.796,2.591-7.338,2.834-10.66,2.584  c-0.552-0.045-1.031,0.371-1.073,0.922c-0.042,0.55,0.371,1.03,0.'.
            '921,1.072c0.665,0.051,1.375,0.082,2.113,0.082  c3.382,0,7.327-0.663,10.058-3.194c2.17-2.011,3.27-4.'.
            '906,3.27-8.605c0-9.099,3.43-11.096,3.447-11.105  C29.591,3.687,29.809,3.219,29.7,2.767z"/></svg>';
        return '<span title="Slim Http Environment">'.$this->icon.'</span>';
    }

    public function getPanel()
    {
        return '<h1>'.$this->icon.' Slim '.$this->ver['slim'].' Http Environment:</h1>
        <div style="overflow: scroll; max-height: 600px;">' . $this->content . '</div>';
    }
}
