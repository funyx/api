<?php
declare(strict_types=1);

namespace funyx\api\Middleware;

use funyx\api\Middleware;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;

class CORSMiddleware extends Middleware
{
	/**
	 * Before anything happens
	 *
	 * @param Event $event
	 * @param Micro $application
	 *
	 * @returns bool
	 */
	public function beforeHandleRoute(Event $event, Micro $application)
	{
		if ($application->request->getHeader('ORIGIN')) {
			$origin = $application->request->getHeader('ORIGIN');
		} else {
			$origin = '*';
		}

		$application
			->response
			->setHeader('Access-Control-Allow-Origin', $origin)
			->setHeader(
				'Access-Control-Allow-Methods',
				'GET,PUT,POST,DELETE,OPTIONS'
			)
			->setHeader(
				'Access-Control-Allow-Headers',
				'Origin, X-Requested-With, Content-Range, ' .
				'Content-Disposition, Content-Type, Authorization'
			)
			->setHeader('Access-Control-Allow-Credentials', 'true');
		if ($application->request->getHeader('ORIGIN') && $application->request->getMethod() === 'OPTIONS') {
			$application->response->setStatusCode(200,'OK')->send();
			exit();
		}
	}
}
