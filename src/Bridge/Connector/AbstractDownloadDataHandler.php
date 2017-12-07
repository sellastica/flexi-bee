<?php
namespace Sellastica\FlexiBee\Bridge\Connector;

use Sellastica\Connector\Model\IDownloadDataHandler;

abstract class AbstractDownloadDataHandler extends \Sellastica\Connector\Model\AbstractDownloadDataHandler implements IDownloadDataHandler
{
	/**
	 * @param string $garbage
	 * @param string $string
	 * @return string
	 */
	protected function extractString(string $garbage, string $string): string
	{
		return trim(str_ireplace($garbage, '', $string));
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	protected function convertValue($value)
	{
		switch ($value) {
			case '':
				return null;
				break;
			case 'true':
				return true;
				break;
			case 'false':
				return false;
				break;
			default:
				return $value;
				break;
		}
	}

	/**
	 * @param \stdClass $data
	 * @return \stdClass
	 */
	protected function convertData(\stdClass $data): \stdClass
	{
		$convertedData = new \stdClass();
		foreach ($data as $key => $value) {
			$convertedData->$key = $this->convertValue($value);
		}

		return $convertedData;
	}
}
