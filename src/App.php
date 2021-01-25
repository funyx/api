<?php
declare(strict_types = 1);

namespace funyx\api;

use atk4\data\Persistence;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Events\Manager;
use Phalcon\Http\Request;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Router;

class App extends Micro
{

	public function __construct( array $config = null )
	{
		$di = new Di();
		if ( !$di->has('config')) {
			$di->set('config', new Config($config), true);
		}
		if ( !$di->has('request')) {
			$di->set("request", Request::class, true);
		}
		if ( !$di->has('response')) {
			$di->set("response", Response::class, true);
		}
		if ( !$di->has('db')) {
			$di->set('db', Persistence::class, true);
		}

		$eventsManager = new Manager();
		$this->setEventsManager($eventsManager);
		if (isset($config['mws'])) {
			foreach ($config['mws'] as $mw) {
				$class = $mw[0];
				$eventsManager->attach('micro', new $class());
				unset($class);
				unset($mw);
			}
		}
		unset($eventsManager);

		parent::__construct($di);
		unset($di);
		$this->setService('router', new Router(), true);
		$this->setService('logger', new Logger(), true);

		if (isset($config['mws'])) {
			foreach ($config['mws'] as $mw) {
				$class = $mw[0];
				$spot = isset($mw['event']) ? $mw['event'] : 'before';
				$this->use($spot, new $class());
				unset($spot);
				unset($mw);
				unset($class);
			}
		}
		if (isset($config['routes'])) {
			foreach ($config['routes'] as $route) {
				[
					$method,
					$pattern,
					$action
				] = $route;
				[
					$action_class,
					$action_method
				] = explode('::', $route[2]);
				$route = $this->getSharedService('router')->add($pattern, [
					'class'  => $action_class,
					'action' => $action_method
				], $method);
				$this->handlers[$route->getId()] = [
					new Micro\LazyLoader($action_class),
					$action_method
				];
			}
		}
		$this->notFound(function ()
		{
			$this->getService('logger')->error('Not Found');
			(new Response())->notFound();
			return false;
		});
	}

	/**
	 * @return \funyx\api\App
	 * @throws \funyx\api\Exception
	 * @var mixed  $middleware - name or instance of a middleware class
	 *
	 * @var string $spot
	 * before   -    Before the handler has been executed
	 * after    -    After the handler has been executed
	 * finish   -    After the response has been sent to the caller
	 *
	 */
	public function use( string $spot, $middleware ): App
	{
		if ( !in_array($spot, [
			'after',
			'before',
			'finish'
		])) {
			throw new Exception("$spot is not supported middleware event");
		}
		if (is_string($middleware)) {
			if ( !class_exists($middleware)) {
				throw new Exception("$middleware middleware does not exist");
			}
			$this->{$spot}(new $middleware());
		} else {
			if (is_a($middleware, Middleware::class)) {
				$this->{$spot}($middleware);
			}
		}
		return $this;
	}

	public function handle( string $uri )
	{
		try {
			parent::handle($uri);
		} catch (\Exception $e) {
			if (is_null($this->activeHandler)) {
				return (new Response())->notFound();
			}
			$this->handleError($e);
		}
	}

	public function handleError( \Exception $e ): void
	{
		switch (get_class($e)) {
			case \Exception::class:
				if (method_exists($e, 'getParams')) {
					$e_msg = json_encode($e->getParams());
					$error_data = $e->getParams();
				} else {
					$e_msg = $e->getMessage();
					$error_data = [$e->getMessage()];
				}
				$this->getService('logger')->error('RES : '.$e_msg);
				break;
			case \atk4\data\Exception::class:
				$p = $e->getParams();
				switch ($e->getMessage()):
					case 'Field is not defined in model':
						$error_data = ['api' => 'Field "'.$p['field'].'" is not available in model "'.$p['model']->caption.'"'];
						$this->getService('logger')->error('RES : '.json_encode($error_data));
						(new Response())->error($error_data);
						die();
						break;
				endswitch;
				switch ($e->getCode()):
					case 404:
						$this->getService('logger')->error('Not Found');
						(new Response())->notFound();
						die();
						break;
				endswitch;
				$error_data = $e->getMyTrace();
				break;
			case \atk4\dsql\ExecuteException::class:
				$error_data = ['dsql' => $e->getParams()['error']];
				$this->getService('logger')->error('RES : '.json_encode($error_data));
				break;
			case \Phalcon\Mvc\Micro\Exception::class:
				$this->getService('logger')->error('RES : '."\n".$e->getTraceAsString());
				$error_data = ['api' => $e->getMessage()];
				break;
			default :
				$error_data = [
					get_class($e) => $e->getMessage()
				];
				$this->getService('logger')->error('RES : '.json_encode($error_data));
		}
		(new Response())->error($error_data);
		die();
	}

	/**
	 * @param \Phalcon\Mvc\Micro\CollectionInterface $collection
	 *
	 * @return \Phalcon\Mvc\Micro
	 */
	public function mount( Micro\CollectionInterface $collection ): Micro
	{
		return parent::mount($collection);
	}
}
