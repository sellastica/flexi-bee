<?php
namespace Sellastica\FlexiBee\Bridge\Connector;

use Sellastica\Connector\Model\DownloadResponse;
use Sellastica\FlexiBee\Changes;
use Sellastica\FlexiBee\Client;

abstract class AbstractDownloadDataGetter extends \Sellastica\Connector\Model\AbstractDownloadDataGetter
{
	/** @var Changes */
	protected $changes;
	/** @var Client */
	protected $client;


	/**
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->changes = new Changes($client);
		$this->client = $client;
	}

	/**
	 * @param string $evidence
	 * @param int $globalVersion
	 * @return \Sellastica\Connector\Model\DownloadResponse
	 */
	public function getChangesResponse(string $evidence, int $globalVersion): DownloadResponse
	{
		//flexibee changes API returns changed records all of flexibee global version number
		//so, do not paginate this response
		//nextOffset attribute in this response returns number of next global version
		//we must increase global version with 1, because it returns changes INCLUSIVE defined number
		$changesResponse = $this->changes->getEvidence($evidence, $globalVersion + 1);

		$changedIds = [];
		$removedIds = [];
		foreach ($changesResponse->winstrom->changes as $item) {
			if (in_array($item->{'@operation'}, ['create', 'update'])) {
				$changedIds[] = $item->id;
			} elseif ($item->{'@operation'} === 'delete') {
				$removedIds[] = $item->id;
			}
		}

		//even if $changedIds is empty, we need whole response to retrieve the global version number
		//do not paginate nor offset this request, its done above already
		$flexiBeeResponse = $this->client->getByIds($evidence, $changedIds, $this->getQuery());
		//provide $changesResponse to DownloadResponse, because we need to get next global version number from it
		$downloadResponse = new DownloadResponse(
			$changesResponse,
			$flexiBeeResponse->winstrom->$evidence
		);
		$downloadResponse->setExternalIdsToRemove($removedIds);

		return $downloadResponse;
	}

	/**
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return array
	 */
	abstract protected function getQuery(int $limit = null, int $offset = null): array;
}