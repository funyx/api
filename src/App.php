<?php
declare(strict_types = 1);

namespace funyx\api;

use atk4\data\Persistence;
use atk4\dsql\ExecuteException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Events\Manager;
use Phalcon\Filter;
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
		if ( !$di->has('filter')) {
			$di->set('filter', Filter::class, true);
		}
		if ( !$di->has('response')) {
			$di->set("response", Response::class, true);
		}
		if ( !$di->has('db')) {
			$di->set('db', Persistence::class, true);
		}
		if ( !$di->has('auth')) {
			$di->set('auth', Auth::class, true);
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
				$this->response->notFound();
			}
			$this->handleError($e);
		}
	}

	public function handleError( \Exception $e ): void
	{
		$error_code = $e->getCode();
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
						$this->response->error($error_data);
						die();
						break;
				endswitch;
				switch ($error_code):
					case 404:
						$this->getService('logger')->error('Not Found');
						$this->response->notFound();
						die();
						break;
				endswitch;
				$error_data = $e->getMyTrace();
				break;
			case ExecuteException::class:
				$error_data = ["dsql" => $e->getParams()['error']];
				$this->getService('logger')->error('RES : '.json_encode($error_data));
				break;
			case \Phalcon\Mvc\Micro\Exception::class:
				$this->getService('logger')->error('RES : '."\n".$e->getTraceAsString());
				$error_data = ["api" => $e->getMessage()];
				break;
			case ExpiredException::class:
				$error_data = ['auth' => 'Expired JWT Token'];
				$this->getService('logger')->error('RES : '.json_encode($error_data));
				break;
			case SignatureInvalidException::class:
				$error_data = ['auth' => 'JWT signature mismatch'];
				$this->getService('logger')->error('RES : '.json_encode($error_data));
				break;
			case Exception::class:
				switch ($error_code):
					case 401:
						$error_data = [
							'auth' => $e->getMessage() ?? 'Unauthorized'
						];
						break;
					default:
						$error_data = [
							get_class($e) => $e->getMessage()
						];
						break;
				endswitch;
				$this->getService('logger')->error('RES : '.json_encode($error_data));
				break;
			default :
				$error_data = [
					get_class($e) => $e->getMessage()
				];
				$this->getService('logger')->error('RES : '.json_encode($error_data));
		}
		if ( !$this->response->isSent()) {
			if ($error_code === 0) {
				$error_code = 500;
			}
			$this->response->error($error_data, $error_code);
		}
		die();
	}
}
