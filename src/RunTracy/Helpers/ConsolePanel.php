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

class ConsolePanel implements IBarPanel
{
    protected $icon;
    protected $cfg;
    protected $terminalJs;
    protected $terminalCss;
    protected $noLogin;

    public function __construct(array $cfg = [])
    {
        $this->cfg = $cfg;
    }


    public function getTab()
    {
        // Set blinker to work with no active panel
        $head = '
        <style type="text/css">
            .warn {
                display: none;
            }
            .warn_blink {
                color: ghostwhite !important;
                background: red !important;
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function(){
                // configure blinker
                if('.$this->cfg['ConsoleNoLogin'].') {
                    function blinker() {
                        $(".warn_blink").fadeOut(800);
                        $(".warn_blink").fadeIn(800);
                    }
                    setInterval(blinker, 1600);
                } else {
                    $( ".warn_blink" ).each(function () {
                        this.style.setProperty( "background", "transparent", "important" );
                    });
                }
            });
        </script>';

        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" '.
            'y="0px" width="16px" height="16px" viewBox="0 0 471.362 471.362" style="enable-background:new '.
            '0 0 471.362 471.362;" xml:space="preserve"><g>
                <path d="M468.794,355.171c-1.707-1.718-3.897-2.57-6.563-2.57H188.145c-2.664,0-4.854,0.853-6.567,2.57 '.
            'c-1.711,1.711-2.565,3.897-2.565,6.563v18.274c0,2.662,0.854,4.853,2.565,6.563c1.713,1.712,3.903,2.57,'.
            '6.567,2.57h274.086    c2.666,0,4.856-0.858,6.563-2.57c1.711-1.711,2.567-3.901,2.567-6.563v-18.274C471.'.
            '365,359.068,470.513,356.882,468.794,355.171z" fill="#444444"/>
                <path d="M30.259,85.075c-1.903-1.903-4.093-2.856-6.567-2.856s-4.661,0.953-6.563,2.856L2.852,99.353 '.
            'C0.95,101.255,0,103.442,0,105.918c0,2.478,0.95,4.664,2.852,6.567L115.06,224.69L2.852,336.896C0.95,'.
            '338.799,0,340.989,0,343.46    c0,2.478,0.95,4.665,2.852,6.567l14.276,14.273c1.903,1.906,4.089,2.854,'.
            '6.563,2.854s4.665-0.951,6.567-2.854l133.048-133.045 c1.903-1.902,2.853-4.096,2.853-6.57c0-2.473-0.95-4.'.
            '663-2.853-6.565L30.259,85.075z" fill="#444444"/></g>
        </svg>';

        return $head . '
        <span title="PTY Console" class="warn_blink">
            ' . $this->icon . '&nbsp; 
        </span>
        ';
    }

    public function getPanel()
    {
        $this->noLogin = $this->cfg['ConsoleNoLogin'];
        $this->terminalJs = $this->cfg['ConsoleTerminalJs'];
        $this->terminalCss = $this->cfg['ConsoleTerminalCss'];

        ob_start();
        require __DIR__ .'../../Templates/ConsolePanel.phtml';
        return ob_get_clean();
    }
}
