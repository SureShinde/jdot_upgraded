<?php
namespace Aalogics\Lcs\Logger;

use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
	/**
	 * @var string
	 */
	protected $fileName = '/var/log/aalcs.log';

	/**
	 * @var int
	 */
	protected $loggerType = Logger::DEBUG;
}