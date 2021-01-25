<?php
declare(strict_types = 1);

namespace funyx\api;

use Phalcon\Http\Response as PhalconResponse;

class Response extends PhalconResponse
{
	public function json( $data ): void
	{
		$this->setStatusCode(200);
		$payload = [
			'status' => 'OK',
			'data'   => $data,
			'error'  => null
		];
		ksort($payload, SORT_NATURAL);
		$this->setJsonContent($payload);
		$this->send();
	}

	public function notFound(): void
	{
		$this->setStatusCode(404);
		$payload = [
			'status' => 'NOT_FOUND',
			'data'   => null,
			'error'  => null
		];
		ksort($payload, SORT_NATURAL);
		$this->setJsonContent($payload);
		$this->send();
	}

	public function notImplemented(): void
	{
		$this->error(['api' => 'Not implemented']);
	}

	/**
	 * @param array|null $dictionary - [err::class = err->getMessage ]
	 * @param int        $code
	 * @param string     $msg
	 */
	public function error( array $dictionary = null, int $code = 500, string $msg = 'ERROR' ): void
	{
		$this->setStatusCode($code);
		$payload = [
			'status' => $msg,
			'data'   => null,
			'error'  => $dictionary
		];
		ksort($payload, SORT_NATURAL);
		$this->setJsonContent($payload);
		$this->send();
	}
}
