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

namespace RunTracy\Helpers\Console;

use RunTracy\Exceptions\IncorrectUserOrPassword;

class WebConsoleRPCServer extends BaseJsonRpcServer
{
    protected $homeDirectory = '';

    protected $noLogin = false;
    protected $accounts = [];
    protected $passwordHashAlgorithm = '';

    // Authentication
    protected function authenticateUser($user, $password)
    {
        $user = trim((string) $user);
        $password = trim((string) $password);

        if ($user && $password) {
            if (isset($this->accounts[$user]) && !$this->isEmptyString($this->accounts[$user])) {
                if ($this->passwordHashAlgorithm) {
                    $password = $this->getHash($this->passwordHashAlgorithm, $password);
                }

                if ($this->isEqualStrings($password, $this->accounts[$user])) {
                    return $user . ':' . $this->getHash('sha256', $password);
                }
            }
        }

        throw new IncorrectUserOrPassword();
    }

    protected function authenticateToken($token)
    {
        if ($this->noLogin) {
            return true;
        }

        $token = trim((string) $token);
        $tokenParts = explode(':', $token, 2);

        if (count($tokenParts) == 2) {
            $user = trim((string) $tokenParts[0]);
            $passwordHash = trim((string) $tokenParts[1]);

            if ($user && $passwordHash) {
                if (isset($this->accounts[$user]) && !$this->isEmptyString($this->accounts[$user])) {
                    $realPasswordHash = $this->getHash('sha256', $this->accounts[$user]);
                    if ($this->isEqualStrings($passwordHash, $realPasswordHash)) {
                        return $user;
                    }
                }
            }
        }

        throw new IncorrectUserOrPassword();
    }

    protected function getHomeDirectory($user)
    {
        if (is_string($this->homeDirectory)) {
            if (!$this->isEmptyString($this->homeDirectory)) {
                return $this->homeDirectory;
            }
        } elseif (is_string($user) && !$this->isEmptyString($user)
            && isset($this->homeDirectory[$user])
            && !$this->isEmptyString($this->homeDirectory[$user])) {
            return $this->homeDirectory[$user];
        }

        return getcwd();
    }

    // Environment
    protected function getEnvironment()
    {
        $hostname = function_exists('gethostname') ? gethostname() : null;
        return ['path' => getcwd(), 'hostname' => $hostname];
    }

    protected function setEnvironment($environment)
    {
        $environment = !empty($environment) ? (array) $environment : [];
        $path = (isset($environment['path']) && !$this->isEmptyString($environment['path'])) ?
            $environment['path'] : $this->homeDirectory;

        if (!$this->isEmptyString($path)) {
            if (is_dir($path)) {
                if (!chdir($path)) {
                    return [
                    'output' => 'Unable to change directory to current working directory, updating current directory',
                    'environment' => $this->getEnvironment()
                    ];
                }
            } else {
                return [
                'output' => 'Current working directory not found, updating current directory',
                'environment' => $this->getEnvironment()
                ];
            }
        }
        return false;
    }

    // Initialization
    protected function initialize($token, $environment)
    {
        $user = $this->authenticateToken($token);
        $this->homeDirectory = $this->getHomeDirectory($user);
        $result = $this->setEnvironment($environment);

        if ($result) {
            return $result;
        }
        return false;
    }

    // Methods
    public function login($user, $password)
    {
        $result = ['token' => $this->authenticateUser($user, $password),
            'environment' => $this->getEnvironment()];

        $homeDirectory = $this->getHomeDirectory($user);
        if (!$this->isEmptyString($homeDirectory)) {
            if (is_dir($homeDirectory)) {
                $result['environment']['path'] = $homeDirectory;
            } else {
                $result['output'] = 'Home directory not found: '. $homeDirectory;
            }
        }

        return $result;
    }

    public function cd($token, $environment, $path)
    {
        $result = $this->initialize($token, $environment);
        if ($result) {
            return $result;
        }

        $path = trim((string) $path);
        if ($this->isEmptyString($path)) {
            $path = $this->homeDirectory;
        }

        if (!$this->isEmptyString($path)) {
            if (is_dir($path)) {
                if (!chdir($path)) {
                    return ['output' => 'cd: '. $path . ': Unable to change directory'];
                }
            } else {
                return ['output' => 'cd: '. $path . ': No such directory'];
            }
        }

        return ['environment' => $this->getEnvironment()];
    }

    public function completion($token, $environment, $pattern)
    {
        $result = $this->initialize($token, $environment);
        if ($result) {
            return $result;
        }

        $scanPath = '';
        $completionPrefix = '';
        $completion = [];

        if (!empty($pattern)) {
            if (!is_dir($pattern)) {
                $pattern = dirname($pattern);
                if ($pattern == '.') {
                    $pattern = '';
                }
            }

            if (!empty($pattern)) {
                if (is_dir($pattern)) {
                    $scanPath = $completionPrefix = $pattern;
                    if (substr($completionPrefix, -1) != '/') {
                        $completionPrefix .= '/';
                    }
                }
            } else {
                $scanPath = getcwd();
            }
        } else {
            $scanPath = getcwd();
        }

        if (!empty($scanPath)) {
            // Loading directory listing
            $completion = array_values(array_diff(scandir($scanPath), ['..', '.']));
            natsort($completion);

            // Prefix
            if (!empty($completionPrefix) && !empty($completion)) {
                foreach ($completion as &$value) {
                    $value = $completionPrefix . $value;
                }
            }

            // Pattern
            if (!empty($pattern) && !empty($completion)) {
                // For PHP version that does not support anonymous functions (available since PHP 5.3.0)
                function filterPattern($value, $pattern)
                {
                    return !strncmp($pattern, $value, strlen($pattern));
                }

                $completion = array_values(array_filter($completion, [$this, 'filterPattern']));
            }
        }

        return ['completion' => $completion];
    }

    public function run($token, $environment, $command)
    {
        $result = $this->initialize($token, $environment);
        if ($result) {
            return $result;
        }

        $output = ($command && !$this->isEmptyString($command)) ? $this->executeCommand($command) : '';
        if ($output && substr($output, -1) == "\n") {
            $output = substr($output, 0, -1);
        }

        return ['output' => $output];
    }

    // Command execution
    private function executeCommand($command)
    {
        $descriptors = [
            0 => ['pipe', 'r'], // STDIN
            1 => ['pipe', 'w'], // STDOUT
            2 => ['pipe', 'w']  // STDERR
        ];

        $process = proc_open($command . ' 2>&1', $descriptors, $pipes);
        if (!is_resource($process)) {
            return 'Can not execute command.';
        }

        // Nothing to push to STDIN
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        // All pipes must be closed before 'proc_close'
        $code = proc_close($process);

        if (!empty($error)) {
            $output .= ', exit wit error:' . $error . ', code: ' . $code;
        }
        return $output;
    }

    // Utilities
    private function isEmptyString($string)
    {
        return strlen($string) <= 0;
    }

    private function isEqualStrings($string1, $string2)
    {
        return strcmp($string1, $string2) == 0;
    }

    private function getHash($algorithm, $string)
    {
        return hash($algorithm, trim((string) $string));
    }
}
