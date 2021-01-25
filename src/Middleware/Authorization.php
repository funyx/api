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
	public $application;
	/**
	 * @var \Phalcon\Config
	 */
	public Config $config;

	/**
	 * @param \Phalcon\Events\Event $event
	 * @param \Phalcon\Mvc\Micro    $application
	 *
	 * @return bool
	 * @throws \funyx\api\Exception
	 */
	public function beforeExecuteRoute( Event $event, Micro $application ): bool
	{
		if(is_a($application, App::class)){
			$this->application = $application;
		}
		$this->loadConfig();
		$this->authorize();
		return true;
	}

	public function authorize($service_data = null): void
	{
		$this->application->setService('authorize', function() use ($service_data){
			return $service_data;
		}, true);
	}

	protected function loadConfig():void
	{
		$config = $this->application->getSharedService('config');
		if(!$config->get('authorization')){
			throw new Exception('Set authorization configuration');
		}
		$this->config = $config->get('authorization');
	}

}
