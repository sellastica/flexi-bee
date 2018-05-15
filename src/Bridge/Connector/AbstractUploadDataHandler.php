<?php
namespace Sellastica\FlexiBee\Bridge\Connector;

use Nette\Utils\Strings;
use Sellastica\Connector\Model\IUploadDataHandler;

abstract class AbstractUploadDataHandler implements IUploadDataHandler
{
	/**
	 * @param string $code
	 * @return string
	 */
	protected function createCode(string $code): string
	{
		return 'code:' . Strings::upper($code);
	}

	/**
	 * @param string|null $string
	 * @return string|null
	 */
	protected function createCodeFromString(?string $string): ?string
	{
		return isset($string) ? Strings::upper(Strings::substring(str_replace('-', '', $string), 0, 20)) : null;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	protected function convertValue($value)
	{
		if (is_null($value)) {
			return '';
		} elseif ($value === true) {
			return 'true';
		} elseif ($value === false) {
			return 'false';
		}

		return $value;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function convertAllValues(array $data): array
	{
		$convertedData = [];
		foreach ($data as $key => $value) {
			$convertedData[$key] = $this->convertValue($value);
		}

		return $convertedData;
	}
}
