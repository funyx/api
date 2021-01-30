<?php
declare(strict_types = 1);

namespace funyx\api\Middleware;

use funyx\api\Middleware;
use Phalcon\Mvc\Micro;

class ResponseMiddleware extends Middleware
{
	/**
	 * @param Micro $application
	 *
	 * @return \Phalcon\Mvc\Micro
	 */
	public function call( Micro $application ): Micro
	{
		if ($application->response && !$application->response->isSent()) {
			if (empty($application->response->getContent())) {
				$returned = $application->getReturnedValue();
				if($returned){
					ksort($returned);
				}
				$application->response->setJsonContent($returned);
			}
			$application->response->send();
		}

		return $application;
	}
}

