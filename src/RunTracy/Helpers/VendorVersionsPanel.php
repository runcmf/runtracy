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

class VendorVersionsPanel implements IBarPanel
{
    private $error;
    private $dir;

    public function __construct($composerLockDir = null)
    {
        if ($composerLockDir === null || !is_string($composerLockDir)) {
            $composerLockDir = realpath(__DIR__ . '/../../../../../../');
        }

        if (!is_dir($dir = realpath($composerLockDir))) {
            $this->error = 'Path "'.$composerLockDir.'" is not a directory.';
        } elseif (!is_file($dir . DIRECTORY_SEPARATOR . 'composer.lock')) {
            $this->error = "Directory '$dir' does not contain the composer.lock file.";
        } else {
            $this->dir = $dir;
        }
    }

    public function getTab()
    {
        return '
        <span title="Vendor Versions">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 512" width="16px" height="16px">
                <path fill="#478CCC" d="M489.349,131.258l0.47-0.462L374.228,15.515l-32.895,32.894l65.485,65.47c-29.'.
            '182,11.174-49.97,39.258-49.97,72.303  c0,42.818,34.758,77.576,77.575,77.576c11.016,0,21.576-2.326,'.
            '31.03-6.516V480.97c0,17.061-13.97,31.03-31.03,31.03  s-31.03-13.97-31.03-31.03V341.333c0-34.287-27.'.
            '772-62.061-62.061-62.061h-31.03V62.061C310.303,27.772,282.53,0,248.242,0H62.061  C27.772,0,0,27.772,'.
            '0,62.061v496.485h310.303V325.818h46.546V480.97c0,42.818,34.758,77.576,77.575,77.576  c42.818,0,77.'.
            '576-34.758,77.576-77.576V186.182C512,164.772,503.303,145.379,489.349,131.258z M248.242,217.'.
            '212H62.061V62.061  h186.182V217.212z M434.424,217.212c-17.061,0-31.03-13.962-31.03-31.03s13.97-31.03,'.
            '31.03-31.03s31.03,13.962,31.03,31.03  S451.484,217.212,434.424,217.212z"/>
            </svg>
        </span>';
    }

    public function getPanel()
    {
        ob_start();

        $jsonFile = $this->dir . DIRECTORY_SEPARATOR . 'composer.json';
        $lockFile = $this->dir . DIRECTORY_SEPARATOR . 'composer.lock';

        if ($this->error === null) {
            $required = array_filter($this->decode($jsonFile));
            $installed = array_filter($this->decode($lockFile));
            $required += ['require' => [], 'require-dev' => []];
            $installed += ['packages' => [], 'packages-dev' => []];
            $data = [
                'Packages' => $this->format($installed['packages'], $required['require']),
                'Dev Packages' => $this->format($installed['packages-dev'], $required['require-dev']),
            ];
        }

        $error = $this->error;

        require __DIR__ .'../../Templates/VendorVersionsPanel.phtml';
        return ob_get_clean();
    }

    private function format(array $packages, array $required)
    {
        $data = [];
        foreach ($packages as $p) {
            $data[$p['name']] = (object) [
                'installed' => $p['version'] . ($p['version'] === 'dev-master'
                        ? (' #' . substr($p['source']['reference'], 0, 7))
                        : ''
                    ),

                'required' => isset($required[$p['name']])
                    ? $required[$p['name']]
                    : null,

                'url' => isset($p['source']['url'])
                    ? preg_replace('/\.git$/', '', $p['source']['url'])
                    : null,
            ];
        }

        ksort($data);
        return $data;
    }

    /**
     * @param  string $file
     * @return array|NULL
     */
    private function decode($file)
    {
        if (!is_file($file)) {
            return null;
        }

        $decoded = json_decode(file_get_contents($file), true);
        if (!is_array($decoded)) {
            $this->error = json_last_error_msg();
            return null;
        }

        return $decoded;
    }

    // test case
    public function getError()
    {
        return $this->error;
    }
}
