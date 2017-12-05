<?php
namespace Sellastica\FlexiBee\Bridge\Connector;

use Nette\Utils\Strings;
use Sellastica\Connector\Model\IUploadDataHandler;

abstract class AbstractUploadDataHandler extends \Sellastica\Connector\Model\AbstractUploadDataHandler implements IUploadDataHandler
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
}
