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
    public $CurlOptions = array(
        CURLOPT_POST => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER => array('Content-Type' => 'application/json'),
    );

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
    private $batchCalls = array();

    /**
     * Batch Notifications
     * @var BaseJsonRpcCall[]
     */
    private $batchNotifications = array();


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
    public function BeginBatch()
    {
        if (!$this->isBatchCall) {
            $this->batchNotifications = array();
            $this->batchCalls = array();
            $this->isBatchCall = true;
            return true;
        }

        return false;
    }


    /**
     * Commit Batch
     */
    public function CommitBatch()
    {
        $result = false;
        if (!$this->isBatchCall || (!$this->batchCalls && !$this->batchNotifications)) {
            return $result;
        }

        $result = $this->processCalls(array_merge($this->batchCalls, $this->batchNotifications));
        $this->RollbackBatch();

        return $result;
    }


    /**
     * Rollback Calls
     * @return bool
     */
    public function RollbackBatch()
    {
        $this->isBatchCall = false;
        $this->batchCalls = array();

        return true;
    }


    /**
     * Process Call
     * @param string $method
     * @param array $parameters
     * @param int $id
     * @return mixed
     */
    protected function call($method, $parameters = array(), $id = null)
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
            $this->processCalls(array($call));
        }

        return $call;
    }


    /**
     * Process Magic Call
     * @param string $method
     * @param array $parameters
     * @return BaseJsonRpcCall
     */
    public function __call($method, $parameters = array())
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
        $result = $this->batchCalls ? array_values(array_map('BaseJsonRpcCall::GetCallData', $calls)) : BaseJsonRpcCall::GetCallData($singleCall);

        // Send Curl Request
        $options = $this->CurlOptions + array(CURLOPT_POSTFIELDS => json_encode($result));
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
                $this->batchCalls[$key]->SetResult($dataCall, $this->UseObjectsInResults);
            }
        } else {
            // Process Results for Call
            $singleCall->SetResult($data, $this->UseObjectsInResults);
        }

        return true;
    }
}
