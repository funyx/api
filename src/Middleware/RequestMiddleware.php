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

		$q = $application->request->getQuery();
		$map = [];
		if (isset($q['data'])) {
			foreach (explode(',', $q['data']) as $field) {
				$value = $this->parseDotedString($field);
				if (is_string($value)) {
					$map[] = $value;
				} elseif (is_array($value)) {
					$map = array_merge_recursive($map, $value);
				}
			}
			if(!empty($map)){
				$application->request->data_map = $map;
			}
		}

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

	private function parseDotedString(string $str): array
	{
		$check = explode('.', $str, 2);
		if (count($check)===2) {
			$value[$check[0]] = $this->parseDotedString($check[1]);
		}else{
			$value = [$check[0]];
		}
		return $value;
	}
}
