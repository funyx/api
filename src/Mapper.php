<?php
declare(strict_types = 1);

namespace funyx\api;

use JmesPath\Env;
use Phalcon\Http\Request;
use Phalcon\Logger;

/**
 * @method init()
 */
class Mapper
{
	public array $data;
	/**
	 * @var \funyx\api\App
	 */
	protected App $app;
	/**
	 * @var \Phalcon\Logger
	 */
	protected Logger $logger;
	/**
	 * @var \Phalcon\Http\Request
	 */
	protected Request $request;
	/**
	 * @var mixed
	 */
	protected $ctx;

	public function __construct( App $app, Logger $logger, Request $request )
	{
		$this->app = $app;
		$this->logger = $logger;
		$this->request = $request;
		if ( !method_exists($this, 'init')) {
			$logger->error('Mapper ('.get_class($this).') must implement protected function init');
		}
		$this->resetCtx($this->request->getJsonRawBody(true) ?? []);
		$this->data = $this->init();
	}

	/**
	 * @param array $ctx
	 *
	 * @return \funyx\api\Mapper
	 */
	public function resetCtx( array $ctx ): Mapper
	{
		$this->ctx = $this->request->getJsonRawBody(true);
		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return array|string|int|bool
	 */
	public function get( string $name )
	{
		return Env::search($name, $this->ctx);
	}

	/**
	 * @param array $ctx
	 *
	 * @return \funyx\api\Mapper
	 */
	public function set( array $ctx ): Mapper
	{
		$this->ctx = $ctx;
		return $this;
	}
}
