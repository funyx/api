<?php

namespace funyx\api;

use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class Middleware implements MiddlewareInterface
{
	/**
	 * @param \Phalcon\Mvc\Micro $application
	 *
	 * @return \Phalcon\Mvc\Micro
	 */
	public function call( Micro $application ): Micro
	{
		return $application;
	}
}
