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


trait TracyTrait
{
    private $prof_timing = [];
    private $prof_names = [];

    public function DBG($var = '')
    {
        \Tracy\Debugger::dump($var);
        $t = debug_backtrace(1, 1);
        //\Tracy\Debugger::barDump($t);
        die('-- im in ' . $t[0]['file'] . ', line: ' . $t[0]['line'] . ' --');
    }

    public function console_log($data)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }

    // Call this at each point of interest, passing a descriptive string
    // http://stackoverflow.com/questions/21133/simplest-way-to-profile-a-php-script/29022400#29022400
    function prof_flag($str)
    {
        $this->prof_timing[] = microtime(true);
        $this->prof_names[] = $str;
    }

    // Call this when you're done and want to see the results
    function prof_print()
    {
        $ret = '';
        $size = count($this->prof_timing);
        for($i=0;$i<$size - 1; $i++)
        {
            $ret .= $this->prof_names[$i]."\n";
            $ret .= sprintf('   %f'."\n", $this->prof_timing[$i+1]-$this->prof_timing[$i]);
        }
        return $ret . $this->prof_names[$size-1]."\n";
    }
}