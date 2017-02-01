<?php
/**
 * Copyright 2017 1f7.wizard@gmail.com
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

/**
 * Class IdiormPanel
 * @package RunTracy\Helpers
 */
class IdiormPanel implements IBarPanel
{
    private $icon;
    private $count;
    private $logs;
    private $time;
    private $parsed;

    public function __construct()
    {
        $this->parsed = $this->parse();
    }

    public function getTab()
    {
        $this->icon = 'ORM';
        return '
        <span title="Idiorm query logs">'.
            $this->icon . ' (' . $this->count . ' / ' . round($this->time * 1000, 2) . ' ms)
        </span>';
    }

    public function getPanel()
    {
        return '
        <h1>'.$this->icon.' &nbsp; Slim 3 / Idiorm</h1>
        <div class="tracy-inner">
            <p>
                <table width="100%">' . $this->parsed . '
                    <tr class="yes">
                        <th><b>' . $this->count . '</b></th><th width="100px"><b>' . $this->time . ' s</b></th>
                        <th>total time</th>
                    </tr>
                </table>
            </p>
        </div>';
    }

    private function parse()
    {
        $this->logs = \RunTracy\Collectors\IdormCollector::getLog();
        if (empty($this->logs)) {
            $this->count = $this->time = 0;
            return '<p><strong>No Logs</strong>, maybe own collector work?</p>';
        }
        $return = '<thead><tr><th><b>Count</b></th><th><b>Time,&nbsp;s</b></th><th>Query</th></tr></thead>';
        $baseRow = '<tr><td>%s</td><td>%s</td><td>%s</td></tr>';
        $time = $cnt = 0;
        foreach ($this->logs as $log) {
            $time += $log['time'];
            $row = $baseRow;
            $return .= sprintf(
                $row,
                ++$cnt,
                number_format($log['time'], 8),
                $log['query']
            );
        }
        $this->count = $cnt;
        $this->time = number_format($time, 8);
        return $return;
    }
}
