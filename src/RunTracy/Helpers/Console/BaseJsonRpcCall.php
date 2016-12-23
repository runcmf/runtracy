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

class BaseJsonRpcCall
{
    /** @var int */
    public $Id;

    /** @var string */
    public $Method;

    /** @var array */
    public $Params;

    /** @var array */
    public $Error;

    /** @var mixed */
    public $Result;


    /**
     * Has Error
     * @return bool
     */
    public function HasError()
    {
        return !empty($this->Error);
    }


    /**
     * @param string $method
     * @param array $params
     * @param string $id
     */
    public function __construct($method, $params, $id)
    {
        $this->Method = $method;
        $this->Params = $params;
        $this->Id = $id;
    }


    /**
     * Get Call Data
     * @param BaseJsonRpcCall $call
     * @return array
     */
    public static function GetCallData(BaseJsonRpcCall $call)
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $call->Id,
            'method' => $call->Method,
            'params' => $call->Params,
        ];
    }


    /**
     * Set Result
     * @param mixed $data
     * @param bool $useObjects
     */
    public function SetResult($data, $useObjects = false)
    {
        if ($useObjects) {
            $this->Error = property_exists($data, 'error') ? $data->error : null;
            $this->Result = property_exists($data, 'result') ? $data->result : null;
        } else {
            $this->Error = isset($data['error']) ? $data['error'] : null;
            $this->Result = isset($data['result']) ? $data['result'] : null;
        }
    }
}
