<?php

namespace GetResponse;

/**
 * GetResponse Class for JSON RPC
 * Implemented only what I need =)
 */
class GetResponseJsonRpc extends GetResponseApi2Base
{
	/**
	 * JsonRPC client
	 *
	 * @access private
	 * @var JsonRPC\Client
	 */
	private $client;

	/**
	 * True for a batch request
	 *
	 * @access private
	 * @var boolean
	 */
	private $isBatch = false;

	/**
	 * Last error during batch
	 *
	 * @access private
	 * @var string
	 */
	private $error;

	/**
	 * @param string $apiKey
	 * @param string $apiUrl
	 * @return void
	 */
	public function __construct($apiKey, $apiUrl = null)
	{
		parent::__construct($apiKey, $apiUrl);

		$this->client = new \JsonRpc\Client($this->apiUrl);
	}

	/**
	 * Start a batch request
	 *
	 * @access public
	 * @return GetResponseJsonRpc
	 */
	public function batch()
	{
		$this->isBatch = true;
		$this->client->batchOpen();

		return $this;
	}

	/**
	 * Return last error
	 *
	 * @access public
	 * @return string
	 */
	public function error()
	{
		return $this->error;
	}

	/**
	 * Send a batch request
	 *
	 * @access public
	 * @return array|false
	 */
	public function send()
	{
		$this->isBatch = false;

		$this->client->batchSend();
		
		if ($this->client->error) {
			$this->error = $this->client->error;
			
			return false;
		}
		
		$response = json_decode($this->client->output, true);

		$result = [];
		foreach ($response as $item) {
			$result[] = isset($item['result'])?$item['result']:$item['error'];
		}

		return $result;
	}

	/**
	 * Executes an API call
	 * @param string $method API method to call
	 * @param array $params Array of parameters
	 * @return array
	 * @access private
	 */
	protected function execute($method, array $params = [])
	{
		$array = array($this->apiKey);

		if (!empty($params)) {
			$array[1] = $params;
		}

		if ($this->isBatch) {
			$this->client->call($method, $array);

			return $this;
		} else {
			$this->client->call($method, $array);
			$response = json_decode($this->client->output, true);

			return isset($response['result'])?$response['result']:$response['error'];
		}
	}
}

