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

class EloquentORMPanel implements IBarPanel
{
    private $count;
    private $data;
    private $time;
    private $icon;

    public function __construct($data = null)
    {
        $this->data = $data;
        $this->count = count($data);
    }

    protected function getHeader()
    {
        return '<thead><tr><th><b>Count</b></th><th><b>Time,&nbsp;ms</b></th><th>Query / Bindings</th></tr></thead>';
    }

    protected function getBaseRow()
    {
        return '<tr><td>%s</td><td>%s</td><td>%s</td></tr>';
    }

    public function getTab()
    {
        $this->data = $this->parse($this->data);
        $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0px" y="0px"viewBox="0 0 284.207 '.
            '284.207" style="enable-background:new 0 0 284.207 284.207;" xml:space="preserve" width="16px" height='.
            '"16px"><path d="M239.604,45.447c0-25.909-41.916-45.447-97.5-45.447s-97.5,19.538-97.5,45.447v47.882c0,'.
            '6.217,2.419,12.064,6.854,17.365  c-3.84,0.328-6.854,3.543-6.854,7.468v47.882c0,6.217,2.419,12.065,6.855'.
            ',17.366c-3.84,0.328-6.855,3.543-6.855,7.468v47.881  c0,25.91,41.916,45.448,97.5,45.448s97.5-19.538,97.'.
            '5-45.448v-47.881c0-3.925-3.016-7.14-6.855-7.468  c4.437-5.301,6.855-11.149,6.855-17.366v-47.882c0-3.'.
            '925-3.015-7.14-6.855-7.468c4.436-5.301,6.855-11.148,6.855-17.365V45.447z M224.598,190.952c-0.121,14.'.
            '354-35.358,30.373-82.494,30.373s-82.373-16.02-82.494-30.373 c16.977,12.544,46.938,20.539,82.494,20.'.
            '539S207.621,203.496,224.598,190.952z M224.598,118.238 c-0.123,14.354-35.359,30.372-82.494,30.372s-82.'.
            '371-16.019-82.494-30.372c16.977,12.543,46.938,20.538,82.494,20.538  S207.621,130.781,224.598,118.238z '.
            'M142.104,15c47.218,0,82.5,16.075,82.5,30.447s-35.282,30.447-82.5,30.447  s-82.5-16.075-82.5-30.447S94.'.
            '886,15,142.104,15z" fill="#005ccc"/></svg>';
        return '
        <span title="DB Query Info">'.$this->icon.'
            <span class="tracy-label">' . $this->count . ' / ' . $this->time . '</span> ms
        </span>';
    }

    public function getPanel()
    {
        return '
        <h1>'.$this->icon.' &nbsp; Slim 3 / Eloquent ORM</h1>
        <div class="tracy-inner">
            <p>
                <table width="100%">' . $this->data . '
                    <tr class="yes">
                        <th><b>' . $this->count . '</b></th><th><b>' . $this->time . ' ms</b></th>
                        <th>Total</th>
                    </tr>
                </table>
            </p>
        </div>';
    }

    private function parse($data)
    {
        $return = $this->getHeader();
        $time = $cnt = 0;
        foreach ($data as $var) {
            $time += $var['time'];
            $row = $this->getBaseRow();
            $bind = '<span class="tracy-dump-hash"><hr />';
            if (!empty($var['bindings'])) {
                foreach ($var['bindings'] as $k => $v) {
                    $bind .= "[$k => $v]<br />";
                }
            }
            $return .= sprintf(
                $row,
                ++$cnt,
                $var['time'],
                $var['query'].$bind.'</span>'
            );
        }
        $this->time = $time;
        return $return;
    }
}
