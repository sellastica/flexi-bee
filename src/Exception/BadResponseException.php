<?php
namespace Sellastica\FlexiBee\Exception;

use Sellastica\Connector\Exception\IErpConnectorException;

class BadResponseException extends \Sellastica\FlexiBee\Exception\FlexiBeeException implements IErpConnectorException
{
}