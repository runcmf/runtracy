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

if (!function_exists('tdie') && class_exists('\Tracy\Debugger')) {
    /**
     * Tracy\Debugger::dump() die with backtrace shortcut
     *
     * @param $var mixed
     */
    function tdie($var = '')
    {
        \Tracy\Debugger::dump($var);
        $t = debug_backtrace(1, 1);
        die('-- im in ' . $t[0]['file'] . ', line: ' . $t[0]['line'] . ' --');
    }
}

if (!function_exists('ttimer') && class_exists('\Tracy\Debugger')) {
    /**
     * Tracy\Debugger::dump() set timer
     *
     * @param $var mixed
     * @return float time
     */
    function ttimer($var = '')
    {
        return \Tracy\Debugger::timer($var);
    }
}

//if (!function_exists('xt_start')) {
//    function xt_start()
//    {
//        xdebug_start_trace(DIR . 'var/log/xdebug/manual_xt_'.date('Y-m-d-H-i-s').'.xt');
//    }
//}
//
//if (!function_exists('xt_stop')) {
//    function xt_stop()
//    {
//        xdebug_stop_trace();
//    }
//}

if (!function_exists('cl')) {
    /**
     * console log
     * @param $data mixed
     * @param $type string (log | info | warn | error)
     */
    function cl($data, $type = 'info')
    {
        echo '<script>';
        switch ($type) {
            case 'log':
                echo 'console.log(' . json_encode($data) . ');'."\n";
                break;
            case 'warn':
                echo 'console.warn(' . json_encode($data) . ');'."\n";
                break;
            case 'error':
                echo 'console.error(' . json_encode($data) . ');'."\n";
                break;
            default:
            case 'info':
                echo 'console.info(' . json_encode($data) . ');'."\n";
                break;
        }
        echo '</script>';
    }
}
