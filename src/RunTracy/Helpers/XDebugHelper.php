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

class XDebugHelper implements IBarPanel
{
    protected $ideKey;


    public function __construct($ideKey = 'PHPSTORM')
    {
        $this->ideKey = $ideKey;
    }

    public function getPanel()
    {
        return false;
    }

    public function getTab()
    {
        ob_start();
        require __DIR__ .'../../Templates/xdebug-helper.tab.phtml';
        return ob_get_clean();
    }
}
