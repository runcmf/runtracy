<?php

declare(strict_types=1);

/**
 * Copyright 2016 1f7.wizard@gmail.com.
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
                background: black !important;
            }
        </style>
        <script type="text/javascript">
            if (typeof jQuery === "function") {
                $(document).ready(function(){
                    // configure blinker
                    if(' . $this->cfg['ConsoleNoLogin'] . ') {
                        function blinker() {
                            $(".warn_blink").fadeOut(800);
                            $(".warn_blink").fadeIn(800);
                        }
                        setInterval(blinker, 1600);
                    } else {
                        $( ".warn_blink" ).each(function () {
                        //    this.style.setProperty( "background", "transparent", "important" );
                            this.style.setProperty( "opacity", "0.2", "important" );
                        });
                    }
                });
            }
        </script>';

        $this->icon = '<svg width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" 
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
        class="feather feather-terminal">
            <polyline points="4 17 10 11 4 5"></polyline>
            <line x1="12" y1="19" x2="20" y2="19"></line>
        </svg>';

        return $head . '
        <span title="PTY Console" class="warn_blink">
            ' . $this->icon . '&nbsp; 
        </span>
        ';
    }

    public function getPanel()
    {
        $this->noLogin     = $this->cfg['ConsoleNoLogin'];
        $this->terminalJs  = $this->cfg['ConsoleTerminalJs'];
        $this->terminalCss = $this->cfg['ConsoleTerminalCss'];

        ob_start();

        require __DIR__ . '../../Templates/ConsolePanel.phtml';

        return ob_get_clean();
    }
}
