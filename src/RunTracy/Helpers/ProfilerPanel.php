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

use RunTracy\Helpers\Profiler\Profiler;
use RunTracy\Helpers\Profiler\Profile;
use RunTracy\Helpers\Profiler\ProfilerService;
use RunTracy\Helpers\Profiler\SimpleProfiler;

use Tracy\IBarPanel;

class ProfilerPanel implements IBarPanel
{
    const CONFIG_PRIMARY_VALUE = 'primaryValue';
    const CONFIG_PRIMARY_VALUE_ABSOLUTE = 'absolute';
    const CONFIG_PRIMARY_VALUE_EFFECTIVE = 'effective';
    const CONFIG_SHOW = 'show';
    const CONFIG_SHOW_MEMORY_USAGE_CHART = 'memoryUsageChart';
    const CONFIG_SHOW_SHORT_PROFILES = 'shortProfiles';
    const CONFIG_SHOW_TIME_LINES = 'timeLines';

    private $profilerService;

    private $config;

    public function __construct(array $config = [])
    {
        $this->config = array_replace_recursive(ProfilerPanel::getDefaultConfig(), $config);

        /** @noinspection PhpInternalEntityUsedInspection */
        $this->profilerService = ProfilerService::getInstance();
    }

    /**
     * @internal
     * @return array
     */
    public static function getDefaultConfig()
    {
        return [
            self::CONFIG_PRIMARY_VALUE => SimpleProfiler::isMemRealUsage() ?
                self::CONFIG_PRIMARY_VALUE_ABSOLUTE : self::CONFIG_PRIMARY_VALUE_EFFECTIVE,
            self::CONFIG_SHOW => [
                self::CONFIG_SHOW_MEMORY_USAGE_CHART => true,
                self::CONFIG_SHOW_SHORT_PROFILES => true,
                self::CONFIG_SHOW_TIME_LINES => true
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTab()
    {
        $countOfProfiles = count($this->profilerService->getProfiles());
        return sprintf(
            '<span title="%s">⏱ %s</span>',
            'Profiler info',
            Profiler::isEnabled() ? sprintf(
                $countOfProfiles == 1 ? '%d profile' : '%d profiles',
                $countOfProfiles
            ) : 'disabled'
        );
    }

    /**
     * @inheritdoc
     */
    public function getPanel()
    {
        $table = '
        <style type="text/css">
            .tracy-inner {
                max-height: calc(100vh - 5px) !important;
            }
        </style>
        ';
        $table .= '<style>.tracy-addons-profiler-hidden{display:none}
            .tracy-addons-profiler-bar{display:inline-block;margin:0;height:0.8em;}</style>';
        $table .= '<table>';
        if ($this->config[self::CONFIG_SHOW][self::CONFIG_SHOW_MEMORY_USAGE_CHART]) {
            $table .= '<tr><td colspan="4" style="text-align: center">' . $this->getMemoryChart() . '</td></tr>';
        }

        if ($this->config[self::CONFIG_PRIMARY_VALUE] == self::CONFIG_PRIMARY_VALUE_ABSOLUTE) {
            $table .= '
            <tr><th>Start</th><th>Finish</th><th>Time (absolute)</th><th>Memory change (absolute)</th></tr>';
        } else {
            $table .= '
            <tr><th>Start</th><th>Finish</th><th>Time (effective)</th><th>Memory change (effective)</th></tr>';
        }
        $this->profilerService->iterateProfiles(function (Profile $profile) use (&$table) {
            /** @noinspection PhpInternalEntityUsedInspection */
            if (!$this->config[self::CONFIG_SHOW][self::CONFIG_SHOW_SHORT_PROFILES] &&
                ($profile->meta[ProfilerService::TIME_LINE_ACTIVE] +
                    $profile->meta[ProfilerService::TIME_LINE_INACTIVE]) < 1) {
                return /* continue */;
            }
            if ($profile->meta[Profiler::START_LABEL] == $profile->meta[Profiler::FINISH_LABEL]) {
                $labels = sprintf(
                    '<td colspan="2">%s</td>',
                    $profile->meta[Profiler::START_LABEL],
                    $profile->meta[Profiler::FINISH_LABEL]
                );
            } else {
                $labels = sprintf(
                    '<td>%s</td><td>%s</td>',
                    $profile->meta[Profiler::START_LABEL],
                    $profile->meta[Profiler::FINISH_LABEL]
                );
            }

            if ($this->config[self::CONFIG_PRIMARY_VALUE] == self::CONFIG_PRIMARY_VALUE_ABSOLUTE) {
                $table .= sprintf(
                    '<tr>%s<td>%d&nbsp;ms (%d&nbsp;ms)</td><td>%d&nbsp;kB (%d&nbsp;kB)</td></tr>',
                    $labels,
                    $profile->duration * 1000,
                    $profile->absoluteDuration * 1000,
                    $profile->memoryUsageChange / 1024,
                    $profile->absoluteMemoryUsageChange / 1024
                );
            } else {
                $table .= sprintf(
                    '<tr>%s<td>%d&nbsp;ms (%d&nbsp;ms)</td><td>%d&nbsp;kB (%d&nbsp;kB)</td></tr>',
                    $labels,
                    $profile->absoluteDuration * 1000,
                    $profile->duration * 1000,
                    $profile->absoluteMemoryUsageChange / 1024,
                    $profile->memoryUsageChange / 1024
                );
            }

            if ($this->config[self::CONFIG_SHOW][self::CONFIG_SHOW_TIME_LINES]) {
                /** @noinspection PhpInternalEntityUsedInspection */
                $table .= sprintf(
                    '<tr class="tracy-addons-profiler-hidden"><td colspan="4"></td></tr><tr><td colspan="4">' .
                    '<span class="tracy-addons-profiler-bar" style="width:%d%%;background-color:#cccccc;"></span>' .
                    '<span class="tracy-addons-profiler-bar" style="width:%d%%;background-color:#3987d4;"></span>' .
                    '<span class="tracy-addons-profiler-bar" style="width:%s%%;background-color:#6ba9e6;"></span>' .
                    '<span class="tracy-addons-profiler-bar" style="width:%s%%;background-color:#cccccc;"></span>' .
                    '</td></tr>',
                    $profile->meta[ProfilerService::TIME_LINE_BEFORE],
                    $profile->meta[ProfilerService::TIME_LINE_ACTIVE],
                    $profile->meta[ProfilerService::TIME_LINE_INACTIVE],
                    $profile->meta[ProfilerService::TIME_LINE_AFTER]
                );
            }
        });

        $table .= '</table>';

        return sprintf(
            '<h1>⏱ Profiler info</h1><div class="tracy-inner">%s</div>',
            Profiler::isEnabled() ? $table : 'Profiling is disabled.'
        );
    }

    private function getMemoryChart()
    {
        $colors = [
            'axis' => '#000000',
            'gridLines' => '#cccccc',
            'memoryUsage' => '#6ba9e6',
            'memoryUsagePoint' => '#3987d4'
        ];
        $margin = 3;
        $maxWidth = 600 - 2 * $margin;
        $maxHeight = 90 - 2 * $margin;
        $gridStep = 10;
        $memoryChart = sprintf(
            '<!--suppress HtmlUnknownAttribute -->
            <svg style="width: %dpx; height: %dpx" viewBox="0 0 %d %d" xmlns="http://www.w3.org/2000/svg">',
            $maxWidth + 2 * $margin,
            $maxHeight + 2 * $margin,
            $maxWidth + 2 * $margin,
            $maxHeight + 2 * $margin
        );
        for ($tmpY = $maxHeight; $tmpY > 0; $tmpY -= $gridStep) {
            $memoryChart .= sprintf(
                '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke-width="1" stroke="%s" />',
                $margin,
                $tmpY + $margin,
                $maxWidth + $margin,
                $tmpY + $margin,
                $colors['gridLines']
            );
        }
        for ($tmpX = $gridStep; $tmpX < $maxWidth; $tmpX += $gridStep) {
            $memoryChart .= sprintf(
                '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke-width="1" stroke="%s" />',
                $tmpX + $margin,
                $margin,
                $tmpX + $margin,
                $maxHeight + $margin,
                $colors['gridLines']
            );
        }

        $firstIteration = true;
        $prevX = 0;
        $prevY = $maxHeight;
        $lines = '';
        $points = '';
        $this->profilerService->iterateMemoryTimeLine(
            function (
                $time,
                $height,
                $metaData
            ) use (
                $colors,
                &$memoryChart,
                $maxWidth,
                $maxHeight,
                $margin,
                &$firstIteration,
                &$prevX,
                &$prevY,
                &$lines,
                &$points
            ) {
            
                if ($firstIteration) {
                    /** @noinspection PhpInternalEntityUsedInspection */
                    $memoryChart .= sprintf(
                        '<text x="%d" y="%d" font-size="%d">%d kB</text>',
                        $margin * 2,
                        10 / 2 + $margin * 2,
                        10,
                        floor($metaData[ProfilerService::META_MEMORY_PEAK] / 1024)
                    );
                        $firstIteration = false;
                }
            /** @noinspection PhpInternalEntityUsedInspection */
                $thisX = floor(max(0, $time) / $metaData[ProfilerService::META_TIME_TOTAL] * $maxWidth);
                if ($thisX == $prevX) {
                    return /* continue */;
                }
                $thisY = floor($maxHeight - $height * $maxHeight / 100);
                $lines .= sprintf(
                    '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke-width="1" stroke="%s" />',
                    $prevX + $margin,
                    $prevY + $margin,
                    $thisX + $margin,
                    $thisY + $margin,
                    $colors['memoryUsage']
                );
                $points .= sprintf(
                    '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke-width="1" stroke="%s" />'.
                    '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke-width="1" stroke="%s" />',
                    $thisX + $margin,
                    $thisY + $margin - 3,
                    $thisX + $margin,
                    $thisY + $margin + 3,
                    $colors['memoryUsagePoint'],
                    $thisX + $margin - 3,
                    $thisY + $margin,
                    $thisX + $margin + 3,
                    $thisY + $margin,
                    $colors['memoryUsagePoint']
                );
                $prevX = $thisX;
                $prevY = $thisY;
            }
        );

        $memoryChart .= $lines . $points;

        $memoryChart .= sprintf(
            '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke-width="1" stroke="%s" />',
            $margin,
            $maxHeight + $margin,
            $maxWidth + $margin,
            $maxHeight + $margin,
            $colors['axis']
        );
        $memoryChart .= sprintf(
            '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke-width="1" stroke="%s" />',
            $margin,
            $margin,
            $margin,
            $maxHeight + $margin,
            $colors['axis']
        );

        $memoryChart .= '</svg>';

        return $memoryChart;
    }
}
