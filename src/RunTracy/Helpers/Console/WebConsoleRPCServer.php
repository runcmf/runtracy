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
    protected $home_directory = '';

    protected $no_login = false;
    protected $accounts = [];
    protected $password_hash_algorithm = '';

    // Authentication
    protected function authenticateUser($user, $password)
    {
        $user = trim((string) $user);
        $password = trim((string) $password);

        if ($user && $password) {
            if (isset($this->accounts[$user]) && !$this->isEmptyString($this->accounts[$user])) {
                if ($this->password_hash_algorithm) {
                    $password = $this->getHash($this->password_hash_algorithm, $password);
                }

                if ($this->isEqualStrings($password, $this->accounts[$user])) {
                    return $user . ':' . $this->getHash('sha256', $password);
                }
            }
        }

//        throw new \Exception('Incorrect user or password');
        throw new IncorrectUserOrPassword();
    }

    protected function authenticateToken($token)
    {

        if ($this->no_login) {
            return true;
        }

        $token = trim((string) $token);
        $token_parts = explode(':', $token, 2);

        if (count($token_parts) == 2) {
            $user = trim((string) $token_parts[0]);
            $password_hash = trim((string) $token_parts[1]);

            if ($user && $password_hash) {
                if (isset($this->accounts[$user]) && !$this->isEmptyString($this->accounts[$user])) {
                    $real_password_hash = $this->getHash('sha256', $this->accounts[$user]);
                    if ($this->isEqualStrings($password_hash, $real_password_hash)) {
                        return $user;
                    }
                }
            }
        }

//        throw new \Exception('Incorrect user or password');
        throw new IncorrectUserOrPassword();
    }

    protected function getHomeDirectory($user)
    {
        if (is_string($this->home_directory)) {
            if (!$this->isEmptyString($this->home_directory)) {
                return $this->home_directory;
            }
        } elseif (is_string($user) && !$this->isEmptyString($user)
            && isset($this->home_directory[$user])
            && !$this->isEmptyString($this->home_directory[$user])) {
            return $this->home_directory[$user];
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
            $environment['path'] : $this->home_directory;

        if (!$this->isEmptyString($path)) {
            if (is_dir($path)) {
                if (!@chdir($path)) {
                    return ['output' => "Unable to change directory to current working directory, 
                    updating current directory",
                    'environment' => $this->getEnvironment()];
                }
            } else {
                return ['output' => "Current working directory not found, updating current directory",
                'environment' => $this->getEnvironment()];
            }
        }
        return false;
    }

    // Initialization
    protected function initialize($token, $environment)
    {
        $user = $this->authenticateToken($token);
        $this->home_directory = $this->getHomeDirectory($user);
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

        $home_directory = $this->getHomeDirectory($user);
        if (!$this->isEmptyString($home_directory)) {
            if (is_dir($home_directory)) {
                $result['environment']['path'] = $home_directory;
            } else {
                $result['output'] = "Home directory not found: ". $home_directory;
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
            $path = $this->home_directory;
        }

        if (!$this->isEmptyString($path)) {
            if (is_dir($path)) {
                if (!@chdir($path)) {
                    return ['output' => "cd: ". $path . ": Unable to change directory"];
                }
            } else {
                return ['output' => "cd: ". $path . ": No such directory"];
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

        $scan_path = '';
        $completion_prefix = '';
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
                    $scan_path = $completion_prefix = $pattern;
                    if (substr($completion_prefix, -1) != '/') {
                        $completion_prefix .= '/';
                    }
                }
            } else {
                $scan_path = getcwd();
            }
        } else {
            $scan_path = getcwd();
        }

        if (!empty($scan_path)) {
            // Loading directory listing
            $completion = array_values(array_diff(scandir($scan_path), ['..', '.']));
            natsort($completion);

            // Prefix
            if (!empty($completion_prefix) && !empty($completion)) {
                foreach ($completion as &$value) {
                    $value = $completion_prefix . $value;
                }
            }

            // Pattern
            if (!empty($pattern) && !empty($completion)) {
                // For PHP version that does not support anonymous functions (available since PHP 5.3.0)
                $this->pattern = $pattern;
                function filterPattern($value)
                {
                    return !strncmp($this->pattern, $value, strlen($this->pattern));
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
            die("Can't execute command.");
        }

        // Nothing to push to STDIN
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        // All pipes must be closed before "proc_close"
        $code = proc_close($process);

        return $output;
    }

//    // Command parsing
//    function parse_command($command)
//    {
//        $value = ltrim((string) $command);
//
//        if (!$this->isEmptyString($value)) {
//            $values = explode(' ', $value);
//            $values_total = count($values);
//
//            if ($values_total > 1) {
//                $value = $values[$values_total - 1];
//
//                for ($index = $values_total - 2; $index >= 0; $index--) {
//                    $value_item = $values[$index];
//
//                    if (substr($value_item, -1) == '\\') $value = $value_item . ' ' . $value;
//                    else break;
//                }
//            }
//        }
//
//        return $value;
//    }

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
