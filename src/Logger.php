<?php


namespace funyx\api;


use funyx\api\Logger\LineFormat;
use Phalcon\Logger\Adapter\Stream;

class Logger extends \Phalcon\Logger
{
	/**
	 * Logger constructor.
	 */
	public function __construct()
	{
		$request_uid = uniqid('req_', true);
		$adapter = new Stream('php://stderr');
		$adapter->setFormatter(new LineFormat($request_uid));
		parent::__construct('default', [$adapter]);
	}
}
