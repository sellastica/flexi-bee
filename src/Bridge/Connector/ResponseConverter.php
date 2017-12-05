<?php
namespace Sellastica\FlexiBee\Bridge\Connector;

use Sellastica\Connector\Model\ConnectorResponse;

class ResponseConverter
{
	/**
	 * Converts FlexiBee API response to ConnectorResponse
	 * @param \stdClass $flexiBeeResponse
	 * @param int $apiStatusCode
	 * @return \Sellastica\Connector\Model\ConnectorResponse
	 */
	public static function convert(\stdClass $flexiBeeResponse, int $apiStatusCode): ConnectorResponse
	{
		$response = !empty($flexiBeeResponse->winstrom->stats->created)
			? ConnectorResponse::created()
			: !empty($flexiBeeResponse->winstrom->stats->updated)
				? ConnectorResponse::modified()
				: new ConnectorResponse($apiStatusCode);
		//external ID
		$response->setExternalId($flexiBeeResponse->winstrom->results[0]->id ?? null);
		//errors
		if (!empty($flexiBeeResponse->winstrom->errors)) {
			foreach ($flexiBeeResponse->winstrom->errors as $error) {
				$response->addError($error->message);
			}
		} elseif (!empty($flexiBeeResponse->winstrom->results[0]->errors)) {
			foreach ($flexiBeeResponse->winstrom->results[0]->errors as $error) {
				$response->addError($error->message);
			}
		} elseif (!empty($flexiBeeResponse->winstrom->success)
			&& $flexiBeeResponse->winstrom->success === 'false') {
			$response->addError($flexiBeeResponse->winstrom->message ?? 'Unknown error');
		}

		return $response;
	}
}