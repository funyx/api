<?php
declare(strict_types = 1);

namespace funyx\api;

use Phalcon\Http\Response as PhalconResponse;
use Phalcon\Http\ResponseInterface;

class Response extends PhalconResponse
{
	public function notFound(): void
	{
		$this->setStatusCode(404);
		$payload = [
			'status' => 'NOT_FOUND',
			'data'   => null,
			'error'  => null
		];
		ksort($payload, SORT_NATURAL);
		$this->json($payload);
		$this->send();
	}

	public function json( $data ): ResponseInterface
	{
		return $this->setJsonContent($data);
	}

	public function setJsonContent( $content, int $jsonOptions = 0, int $depth = 512 ): ResponseInterface
	{
		$status = 'OK';
		if ( !empty($this->getReasonPhrase())) {
			$status = str_replace(' ', '_', strtoupper($this->getReasonPhrase()));
		}
		$content = [
			'status' => $status,
			'data'   => $content,
			'error'  => null
		];
		ksort($content, SORT_NATURAL);
		return parent::setJsonContent($content, $jsonOptions, $depth);
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
			"status" => $msg,
			"data"   => null,
			"error"  => $dictionary
		];
		ksort($payload, SORT_NATURAL);
		// flip slashes
		$payload = str_replace('\\\\', '/', json_encode($payload, JSON_UNESCAPED_SLASHES));
		parent::setContent($payload);
		$this->setHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->send();
	}

	public function rawJson( $content, int $jsonOptions = 0, int $depth = 512 ): ResponseInterface
	{
		ksort($content, SORT_NATURAL);
		return parent::setJsonContent($content, $jsonOptions, $depth);
	}

	public function status( int $code, string $message = null ): ResponseInterface
	{
		return $this->setStatusCode($code, $message);
	}
}
