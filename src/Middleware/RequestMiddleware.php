<?php
declare(strict_types=1);

namespace funyx\api\Middleware;


use funyx\api\Exception;
use funyx\api\Middleware;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;

class RequestMiddleware extends Middleware
{
	/**
	 * @param Event $event
	 * @param Micro $application
	 *
	 * @returns bool
	 * @throws \funyx\api\Exception
	 */
	public function beforeExecuteRoute( Event $event, Micro $application ): bool
	{
		$body = $application->request->getRawBody();

		if (empty($body)) {
			return true;
		}

		json_decode($body);

		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new Exception('Malformed json payload');
		}

		return true;

	}
}
