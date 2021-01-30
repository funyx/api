<?php
declare(strict_types = 1);

namespace funyx\api;

use funyx\api\Data\AccountUser\AccountUserAuth;
use funyx\api\Middleware\Authorization;
use Phalcon\Di\AbstractInjectionAware;

class Auth extends AbstractInjectionAware
{
	protected AccountUserAuth $model;
	protected Authorization $strategy;
	protected string $strategy_id;
	protected array $data_map = ['iat','exp'];

	public function setProvider( string $strategy_id, Authorization $instance ): void
	{
		$this->strategy_id = $strategy_id;
		$this->strategy = $instance;
		// check if the payload is ok
		$this->strategy->init();
		// auto-load persistence
		$db = $this->container->getService('db');
		if ($db && !$db->isResolved()) {
			$c = $this->container->getShared('config');
			$this->container->setShared('db', $db->getDefinition()::connect($c->get('database')->get('dsn')));
		}
		// set model
		$this->setModel(new AccountUserAuth($this->container->getShared('db')));
		$this->strategy->useModel($this->model);
	}

	public function getModel(): AccountUserAuth
	{
		return $this->model;
	}

	public function setModel( AccountUserAuth $model ): void
	{
		$this->model = $model;
	}

	public function getData(): array
	{
		if ($this->getModel()->loaded()) {
			return $this->model->publicUserData([
				$this->strategy_id => array_filter(
					$this->strategy->getData(),
					function($v){
						return in_array($v, $this->data_map);
					},
					ARRAY_FILTER_USE_KEY
				)
			]);
		} else {
			return [];
		}
	}
}
