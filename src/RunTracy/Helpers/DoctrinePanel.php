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
 * Class DoctrinePanel
 * @package RunTracy\Helpers
 */
class DoctrinePanel implements IBarPanel
{
    private $icon;
    private $parsed;
    private $count;
    private $time;

    public function __construct($logs = null)
    {
        $this->parsed = $this->parse(
            $logs
        );
    }

    public function getTab()
    {
        $this->icon = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACUAAAAyCAYAAADbTRIgAAAAAXNSR0IArs4c'.
            '6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAadEVYdFNvZnR3YXJlAFBhaW50Lk5FVCB2My41LjEwMPRyoQ'.
            'AABONJREFUWEe9WFtsVEUYrtEQE4zRqA8mBqJGHlTWiBdAUXxAH9QEL2gQFJSrGoFtK4Ix3q8J1kIVCREEEjCaqNEY0QcevEIDRKi0'.
            'SQm2AhZdlaqlu3vO7O454/ef/q175sycy7qHL/nS7cz8//ftnNl/5kxDFIpLM2dZSzLPgj34XMbff8AP8flyHnJyAeELYOAwKDW0wJ'.
            'k89OQBopurTOhYAe/m4enDahxPpv6oMmCiDU7jsHSRz14xCmJulXgYB4tLMhM5ND0Uh2bqT0U8jP0wlv7ih9B6RTiKx2DsQg5PB/j1'.
            'nQ+hnCIcxUMUxynSAb75VRAaUISj2AFjZ3OKdABjN0GI6pLOgInfw9hoTpEOIDIdpIquM2DilzA2ilOkA4jMBeOWiWF+UFyWOZVTpA'.
            'OIZBXROFyfz44/hVPUH/mmy8jYi4poHL6KgsxZ6ogCCirWyGQIbFcE43I5p6oPYOYSJP1UEUlKWo8LOGXtyC/LnIaS8BSSJS0JJtLJ'.
            'YganTw7Mzlgk2FWVsF6kk8UtLBMfMDQVgeEbcuPVUqy6T9ovTdf3h7MATmG5aOBx3YUA+ja6ZB5LG5uke6JfDsM5fEDar9ypHRtCOl'.
            'mMY1kzMEN3YHBJCfbRfv42KSsltlOF4gkpXp+tjQlhFzTPYPkg0DkJg4pKUIClbc+wCw2swVqMvcMW/IChc9DZpwzWUrTOYQcGFGGs'.
            '5QFtrIFUKoJHajRuqRoUSad7FzswwMpL8UYiY52+PRKzdCUaE2209pNTpdt3kB0YQMYwq7p4A2exJW+WtiqdsWivvEE6R7vYgQE2jK'.
            '1+UBuvYTttZQ2FpZkz8U/k4jbRfuJ66fz8IzswwC7ENoanNo5q0gxdZyIuv046PT+wAwPiG2uiR9emNI7QfvpmKdYuluLtR6R4a6EU'.
            'bfPBeV5y0TrXW8iiZTZKwCwp1syT7vE+dmAA6pj9cmSB/YhMfaU0Sis7QVb2bpfSdTlb/eDmeqXVdI1fz89uMhW4wCh/vIpTpIPyJ6'.
            '0+PYV5MjWoNErnYDuHpwO3r9unp9AlU4E3FKd3P4enAzfX49NTKMiUUBpl+fO1HJ4Oyp+1+fQU5shU8LW8+VpZ2b8jnYXef0xaj0/y'.
            '6/m5m0x9pzSO0F55o/cTFi33S/HmgqHSsO5RLhGLUB7QtuYhbyuh/92/fmVpA8oCZSRy29lEplYrjclJxbN3HysbUC7J0oZGfXwVUc'.
            'wXkalb1Y5E9LaZDlY2QBQxuw/r4/10sc2MoRMC3dj9rnTGor1iinSOhG/IbmEgziMb5jdyxUUjp4TnlM5o4qUhar9zB45L+7V79PEa'.
            '4tHdO+QIwGzRXXmcS9cRlndsZmk96Fdmv3C7NtbAAwX1IgSNc5RBRtIBT/viwHB/6/E2c12sgbSWgsfhQta7eH1fGawlvRiY4Bzp9E'.
            'qJLi6EG9hGEHA7GgP2KgFBori6f+fYxn9wDu1BeZisjzGzAwfN8Bs/GDsPAzuVwABpAdOseEANqnz9XtSRRMej0BvD0uHAwHMREOsO'.
            'gcqC1TxR2xfBn6BzMUvGA6b0dASuA5NeJ8bhF/TFWSo5UDumIUmXkrRW5pBv/mA28/+vGql+INlMJP0WrGXm9oGPRS7oWkELEwILwX'.
            'fBdpBe9elCjS7D6O8v4E5wI7iY1o3dfClHx0FDw7+Sb2560wLhYgAAAABJRU5ErkJggg==" style="height: 15px" />';

        return '
        <span class="tracy-label" title="Doctrine DBAL Panel">
            '.$this->icon.' ('.$this->count . ' / ' . round($this->time * 1000, 2).' ms)
        </span>';
    }

    public function getPanel()
    {
        return '
        <h1>'.$this->icon.' &nbsp; Slim 3 / Doctrine DBAL</h1>
        <div class="tracy-inner doctrinePanel">
            <p>
                <table width="100%">' . $this->parsed . '
                    <tr class="yes">
                        <th><b>' . $this->count . '</b></th>
                        <th style="white-space: nowrap"><b>' . $this->time . ' s</b></th>
                        <th colspan="3">total time</th>
                    </tr>
                </table>
            </p>
        </div>';
    }

    protected function parse($logs)
    {
        $return = '<thead><tr><th>Cnt</th><th>Time (s)</th><th>SQL</th><th>Params</th><th>Types</th></tr></thead>';
        $baseRow = '<tr><td>%s</td><td>%s</td><td>%s</td><td><pre>%s</pre></td><td><pre>%s</pre></td></tr>';
        $time = $cnt = 0;
        foreach ($logs as $log) {
            if (!isset($log['executionMS'])) {
                continue;
            }
            $time = $log['executionMS'];
            $row = $baseRow;
            $return .= sprintf(
                $row,
                ++$cnt,
                number_format($log['executionMS'], 8),
                $log['sql'],
                $this->formatArrayData($log['params']),
                $this->transformNumericType($this->formatArrayData($log['types']))
            );
        }
        $this->count = $cnt;
        $this->time = number_format($time, 8);
        return $return;
    }

    /**
     * function from macfja/tracy-doctrine-sql
     * @param $data
     * @return mixed
     */
    protected function formatArrayData($data)
    {
        return preg_replace(
            '#^\s{4}#m',
            '', // Remove 1rst "tab" of the JSON result
            substr(
                json_encode($data, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK),
                2, // Remove "[\n"
                -2 // Remove "\n]"
            )
        );
    }

    /**
     * function from macfja/tracy-doctrine-sql
     * @param $data
     * @return mixed
     */
    protected function transformNumericType($data)
    {
        $search = [
            '#\b101\b#', // Array of int
            '#\b102\b#', // Array of string
        ];
        $replace = [
            'integer[]', // Array of int
            'string[]', // Array of string
        ];
        return preg_replace($search, $replace, $data);
    }
}
