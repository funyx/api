<?php
declare(strict_types = 1);

namespace funyx\api\Middleware;

use funyx\api\App;
use funyx\api\Exception;
use funyx\api\Middleware;
use Phalcon\Config;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;

class Authorization extends Middleware
{
	/**
	 * @var App
	 */
	public $app;
	/**
	 * @var \Phalcon\Config
	 */
	public Config $config;

	public string $strategy_id;
	protected array $data;

	/**
	 * @param \Phalcon\Events\Event $event
	 * @param \Phalcon\Mvc\Micro    $app
	 *
	 * @return bool
	 * @throws \funyx\api\Exception
	 */
	public function beforeExecuteRoute( Event $event, Micro $app ): bool
	{
		if(is_a($app, App::class)){
			$this->app = $app;
		}
		$this->setConfig();
		$this->app->auth->setProvider($this->strategy_id, $this);
		return true;
	}

	protected function setConfig():void
	{
		if(!$this->app->config->get('authorization')){
			throw new Exception('Set authorization configuration');
		}
		$this->config = $this->app->config->get('authorization');
	}

	public function getData():array
	{
		return $this->data;
	}
}
