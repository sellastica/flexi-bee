<?php
namespace Sellastica\FlexiBee\Exception;

use Sellastica\Connector\Exception\IErpConnectorException;

/**
 * Response does not have expected format
 */
class InvalidResponseException extends BadResponseException implements IErpConnectorException
{
}