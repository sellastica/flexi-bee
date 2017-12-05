<?php
namespace Sellastica\FlexiBee;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Sellastica\Utils\Urls;

class Client
{
	const APPLICATION_CODE = 'flexi_bee';

	/** @var string */
	private $url;
	/** @var string */
	private $user;
	/** @var string */
	private $password;
	/** @var string */
	private $company;

	/** @var array */
	private $defaultUrlParams = [];

	/** @var string|null */
	private $lastCalledUrl;
	/** @var int|null */
	private $lastStatusCode;
	/** @var mixed */
	private $lastResponseRaw;
	/** @var mixed|null */
	private $lastResponse;
	/** @var array|null */
	private $lastHeaders;
	/** @var array|null */
	private $lastCalledBody;
	/** @var string|null */
	private $lastCalledBodyJson;


	/**
	 * @param string $url
	 * @param string $user
	 * @param string $password
	 * @param string $company
	 */
	public function __construct(
		string $url,
		string $user,
		string $password,
		string $company
	)
	{
		$this->url = rtrim($url, '/');
		$this->user = $user;
		$this->password = $password;
		$this->company = $company;
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array|null $body
	 * @return array
	 * @throws \Sellastica\Connector\Exception\InvalidCredentialsException
	 * @throws \Sellastica\Connector\Exception\InvalidResponseException
	 */
	public function request(string $method, string $url, array $body = null)
	{
		$this->lastCalledUrl = $url;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_USERPWD, $this->user . ':' . $this->password);

		$this->lastCalledBody = $body;
		if (isset($body)) {
			$body = $this->lastCalledBodyJson = json_encode($body, JSON_PRETTY_PRINT);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		}

		//headers
		$headers = [
			'Content-Type: application/json',
			'Accept: application/json',
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$this->lastHeaders = $headers;
		$this->lastResponse = $this->lastResponseRaw = curl_exec($ch);
		$this->lastStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$errno = curl_errno($ch);
		curl_close($ch);

		if ($errno) {
			throw new \Sellastica\Connector\Exception\InvalidResponseException('cURL responsed with error code ' . $errno);
		} elseif ($this->lastStatusCode === 401) {
			throw new \Sellastica\Connector\Exception\InvalidCredentialsException('Invalid credentials', 401);
		} else {
			//f($this->lastResponse);
			//g($this->lastStatusCode);
			return $this->lastResponse = $this->decodeJsonResponse($this->lastResponse);
		}
	}

	/**
	 * @param $response
	 * @return array
	 * @throws \Sellastica\Connector\Exception\InvalidResponseException
	 */
	private function decodeJsonResponse($response)
	{
		try {
			return Json::decode($response);
		} catch (JsonException $e) {
			throw new \Sellastica\Connector\Exception\InvalidResponseException('Response is not in JSON format');
		}
	}

	/**
	 * @return array
	 */
	public function getDefaultUrlParams(): array
	{
		return $this->defaultUrlParams;
	}

	/**
	 * @param array $defaultUrlParams
	 */
	public function setDefaultUrlParams(array $defaultUrlParams)
	{
		$this->defaultUrlParams = $defaultUrlParams;
	}

	/**
	 * @param string $resource
	 * @param array $params
	 * @return array|mixed
	 */
	public function get(string $resource, array $params = [])
	{
		$url = $this->buildUrl($resource, http_build_query(array_merge($this->defaultUrlParams, $params)));
		return $this->request('GET', $url);
	}

	/**
	 * @param string $evidence
	 * @param array $ids
	 * @param array $params
	 * @return mixed
	 */
	public function getByIds(string $evidence, array $ids, array $params = [])
	{
		$url = $this->buildUrl("$evidence/get", http_build_query(array_merge($this->defaultUrlParams, $params)));
		$body = [
			'winstrom' => [
				'id' => $ids,
			],
		];
		return $this->request('PUT', $url, $body);
	}

	/**
	 * @param string $resource
	 * @param array $body
	 * @param array $params
	 * @return mixed
	 */
	public function post(string $resource, array $body = [], array $params = [])
	{
		$url = $this->buildUrl($resource, http_build_query($params));
		return $this->request('POST', $url, $this->bodyToFlexiBeeFormat($resource, $body));
	}

	/**
	 * @param string $resource
	 * @param array $body
	 * @param array $params
	 * @return mixed
	 */
	public function put(string $resource, array $body, array $params = [])
	{
		$url = $this->buildUrl($resource, http_build_query($params));
		return $this->request('PUT', $url, $this->bodyToFlexiBeeFormat($resource, $body));
	}

	/**
	 * @return mixed
	 */
	public function getLastResponse()
	{
		return $this->lastResponse;
	}

	/**
	 * @return null|string
	 */
	public function getLastCalledUrl(): ?string
	{
		return $this->lastCalledUrl;
	}

	/**
	 * @return int|null
	 */
	public function getLastStatusCode()
	{
		return $this->lastStatusCode;
	}

	/**
	 * @return mixed
	 */
	public function getLastResponseRaw()
	{
		return $this->lastResponseRaw;
	}

	/**
	 * @return array|null
	 */
	public function getLastHeaders()
	{
		return $this->lastHeaders;
	}

	/**
	 * @return array|null
	 */
	public function getLastCalledBody()
	{
		return $this->lastCalledBody;
	}

	/**
	 * @return null|string
	 */
	public function getLastCalledBodyJson()
	{
		return $this->lastCalledBodyJson;
	}

	/**
	 * @param string $resource
	 * @param array $body
	 * @return array
	 */
	private function bodyToFlexiBeeFormat(string $resource, array $body): array
	{
		return [
			'winstrom' => [
				'@version' => '1.0',
				$resource => [$body],
			],
		];
	}

	/**
	 * @param string $resource
	 * @param string|null $query
	 * @return string
	 */
	private function buildUrl(string $resource, string $query = null): string
	{
		return $this->url . '/c/' . $this->company . '/' . $resource . '.json'
			. ($query ? '?' . Urls::decodeReserved($query) : null);
	}
}