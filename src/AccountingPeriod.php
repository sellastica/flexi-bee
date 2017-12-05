<?php
namespace Sellastica\FlexiBee;

class AccountingPeriod
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
	 * @return int|null
	 */
	public function getCurrentId(): ?int
	{
		$result = $this->client->get('ucetni-obdobi', [
			'kod' => (new \DateTime())->format('Y'),
			'detail' => 'custom',
			'limit' => 1,
		]);
		return $result->winstrom->{'ucetni-obdobi'}[0]->id ?? null;
	}
}