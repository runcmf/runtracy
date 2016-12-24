<?php

namespace RunTracy\Helpers\Console;

/**
 * Base JSON-RPC 2.0 Client
 * @package    Eaze
 * @subpackage Model
 * @author     Sergeyfast
 * @link       http://www.jsonrpc.org/specification
 */
class BaseJsonRpcClient
{

    /**
     * Use Objects in Result
     * @var bool
     */
    public $UseObjectsInResults = false;

    /**
     * Curl Options
     * @var array
     */
    public $CurlOptions = [
        CURLOPT_POST => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER => ['Content-Type' => 'application/json'],
    ];

    /**
     * Current Request id
     * @var int
     */
    private $id = 1;

    /**
     * Is Batch Call Flag
     * @var bool
     */
    private $isBatchCall = false;

    /**
     * Batch Calls
     * @var BaseJsonRpcCall[]
     */
    private $batchCalls = [];

    /**
     * Batch Notifications
     * @var BaseJsonRpcCall[]
     */
    private $batchNotifications = [];


    /**
     * Create New JsonRpc client
     * @param string $serverUrl
     * @return BaseJsonRpcClient
     */
    public function __construct($serverUrl)
    {
        $this->CurlOptions[CURLOPT_URL] = $serverUrl;
    }


    /**
     * Get Next Request Id
     * @param bool $isNotification
     * @return int
     */
    protected function getRequestId($isNotification = false)
    {
        return $isNotification ? null : $this->id++;
    }


    /**
     * Begin Batch Call
     * @return bool
     */
    public function beginBatch()
    {
        if (!$this->isBatchCall) {
            $this->batchNotifications = [];
            $this->batchCalls = [];
            $this->isBatchCall = true;
            return true;
        }

        return false;
    }


    /**
     * Commit Batch
     */
    public function commitBatch()
    {
        $result = false;
        if (!$this->isBatchCall || (!$this->batchCalls && !$this->batchNotifications)) {
            return $result;
        }

        $result = $this->processCalls(array_merge($this->batchCalls, $this->batchNotifications));
        $this->rollbackBatch();

        return $result;
    }


    /**
     * Rollback Calls
     * @return bool
     */
    public function rollbackBatch()
    {
        $this->isBatchCall = false;
        $this->batchCalls = [];

        return true;
    }


    /**
     * Process Call
     * @param string $method
     * @param array $parameters
     * @param int $id
     * @return mixed
     */
    protected function call($method, $parameters = [], $id = null)
    {
        $method = str_replace('_', '.', $method);
        $call = new BaseJsonRpcCall($method, $parameters, $id);
        if ($this->isBatchCall) {
            if ($call->Id) {
                $this->batchCalls[$call->Id] = $call;
            } else {
                $this->batchNotifications[] = $call;
            }
        } else {
            $this->processCalls([$call]);
        }

        return $call;
    }


    /**
     * Process Magic Call
     * @param string $method
     * @param array $parameters
     * @return BaseJsonRpcCall
     */
    public function __call($method, $parameters = [])
    {
        return $this->call($method, $parameters, $this->getRequestId());
    }


    /**
     * Process Calls
     * @param BaseJsonRpcCall[] $calls
     * @return mixed
     */
    protected function processCalls($calls)
    {
        // Prepare Data
        $singleCall = !$this->isBatchCall ? reset($calls) : null;
        $result = $this->batchCalls ? array_values(array_map('BaseJsonRpcCall::getCallData', $calls)) :
            BaseJsonRpcCall::getCallData($singleCall);

        // Send Curl Request
        $options = $this->CurlOptions + [CURLOPT_POSTFIELDS => json_encode($result)];
        $ch = curl_init();
        curl_setopt_array($ch, $options);

        $data = curl_exec($ch);
        $data = json_decode($data, !$this->UseObjectsInResults);
        curl_close($ch);
        if ($data === null) {
            return false;
        }

        // Process Results for Batch Calls
        if ($this->batchCalls) {
            foreach ($data as $dataCall) {
                // Problem place?
                $key = $this->UseObjectsInResults ? $dataCall->id : $dataCall['id'];
                $this->batchCalls[$key]->setResult($dataCall, $this->UseObjectsInResults);
            }
        } else {
            // Process Results for Call
            $singleCall->setResult($data, $this->UseObjectsInResults);
        }

        return true;
    }
}
