<?php
namespace Sellastica\FlexiBee;

use Sellastica\Connector\Model\ConnectorResponse;

/**
 * @see https://www.flexibee.eu/api/dokumentace/ref/changes-api/
 */
class Changes
{
	/** @var Client */
	private $client;

	/**
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * @return \Sellastica\Connector\Model\ConnectorResponse
	 */
	public function enable(): ConnectorResponse
	{
		$response = $this->client->post('changes/enable');
		return Bridge\Connector\ResponseConverter::convert($response, $this->client->getLastStatusCode());
	}

	/**
	 * @return \Sellastica\Connector\Model\ConnectorResponse
	 */
	public function disable(): ConnectorResponse
	{
		$response = $this->client->post('changes/disable');
		return Bridge\Connector\ResponseConverter::convert($response, $this->client->getLastStatusCode());
	}

	/**
	 * @return bool
	 */
	public function getStatus(): bool
	{
		$status = $this->client->get('changes/status');
		if (empty($status->winstrom->success)) {
			return false;
		}

		return ($this->client->getLastStatusCode() === 200
			&& !empty($status->winstrom->success)
			&& $status->winstrom->success === 'true');
	}

	/**
	 * @param string $evidence
	 * @param int $sinceGlobalVersion
	 * @param array $query
	 * @return mixed
	 * @throws \Sellastica\FlexiBee\Exception\InvalidResponseException
	 */
	public function getEvidence(string $evidence, int $sinceGlobalVersion = 0, $query = [])
	{
		$query = array_merge($query, ['start' => $sinceGlobalVersion]);
		$query = array_merge($query, ['evidence' => $evidence]);

		$defaultParams = $this->client->getDefaultUrlParams();
		$this->client->setDefaultUrlParams([]); //need to call without default params
		$response = $this->client->get('changes', $query);
		$this->client->setDefaultUrlParams($defaultParams);

		return $response;
	}
}