<?php
declare(strict_types=1);

namespace funyx\api\Middleware;

use funyx\api\Middleware;
use funyx\api\Response;
use Phalcon\Mvc\Micro;

class ResponseMiddleware extends Middleware
{
	/**
	 * @param Micro $application
	 *
	 * @return \Phalcon\Mvc\Micro
	 */
	public function call(Micro $application): Micro
	{
		(new Response())->json($application->getReturnedValue());

		return $application;
	}
}

