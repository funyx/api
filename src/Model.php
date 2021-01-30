<?php

namespace funyx\api;

use atk4\data\Model as atkModel;
use atk4\data\Reference\HasOne;
use DateTime;
use Phalcon\Http\RequestInterface;
use Phalcon\Mvc\Router\RouteInterface;
use Phalcon\Mvc\RouterInterface;

class Model extends atkModel
{
	public string $datetime_format = DateTime::ATOM;
	public array $data_map = [];
	protected App $app;
	protected RouteInterface $route;
	protected RouterInterface $router;
	protected RequestInterface $req;
	protected Response $res;
	/**
	 * @var bool $has_created_at
	 */
	protected bool $has_created_at = false;
	/**
	 * @var bool $has_updated_at
	 */
	protected bool $has_updated_at = false;
	/**
	 * @var array
	 */
	protected array $payload;

	public function __construct( $persistence = null, $defaults = [] )
	{

		$this->app = $GLOBALS['app'];
		$this->router = $this->app->getRouter();
		$this->route = $this->router->getMatchedRoute();
		$this->req = $this->app->request;
		$this->res = $this->app->response;
		$this->payload = $this->req->getJsonRawBody(true) ?? [];

		$action = $this->route->getPaths()['action'];
		$class = $this->route->getPaths()['class'];
		if (get_class($this) === $class && !method_exists($this, $action)) {
			throw new Exception(get_class($this).' has no action '.$action);
		}
		if (get_class($this) === $class && method_exists($this, $action)) {
			// check if the data_map is set by the request middleware
			if (property_exists($this->req, 'data_map')) {
				$this->data_map = $this->req->data_map;
			}
		}
		if (is_null($this->persistence) && is_null($persistence)) {
			$db = $s = $this->app->getDI()->getService('db');
			if ($db && !$db->resolve()) {
				$config = $this->app->getDI()->getShared('config');
				$this->app->getDI()->setShared('db', $db->getDefinition()::connect($config->get('database')->get('dsn')));
			}
			$this->persistence = $this->app->getDI()->getShared('db');
			$this->init();
		}
		parent::__construct($persistence, $defaults);
		if ($this->has_created_at) {
			$this->addCreatedAt();
		}
		if ($this->has_updated_at) {
			$this->addUpdatedAt();
			$this->onHook('beforeUpdate', function ( Model $m )
			{
				if ($m->hasField('updated_at')) {
					$m['updated_at'] = new DateTime();
				}
			});
		}
	}

	public function addCreatedAt()
	{
		$this->addField('created_at', [
			'type'     => 'datetime',
			'system'   => true,
			'required' => true,
			'default'  => new DateTime
		]);
	}

	public function addUpdatedAt()
	{
		$this->addField('updated_at', [
			'type'     => 'datetime',
			'system'   => true,
			'required' => true
		]);
	}

	public function format(): array
	{
		$data = [];
		if ($this->loaded()) {
			// check refs self::data_map
			foreach ($this->data_map as $key => $value) {
				// support $ref => $sub_ref
				if ( !is_numeric($key)) {
					$ref = $key;
				} else {
					if ($this->hasField($value)) {
						$field_name = $value;
						$field_value = $this->get($field_name);
						if (is_a($field_value, $this->getField($field_name)->dateTimeClass)) {
							$field_value = $field_value->format($this->datetime_format);
						}
						$data[$field_name] = $field_value;
						unset($field_name);
						unset($field_value);
						continue;
					}
					$ref = $value;
				}
				if ($this->hasRef($ref)) {
					$target = $this->ref($ref);
					if (is_a($this->getRef($ref), HasOne::class)) {
						if ( !$target->loaded()) {
							$data[$ref] = null;
							continue;
						}
					}
					if ( !is_numeric($key)) {
						if (is_string($value)) {
							$value = [$value];
						}
						$target->data_map = $value;
					}
					$data[$ref] = $target->format();
				}
			}
		} else {
			foreach ($this->getIterator() as $record) {
				$data[] = $record->format();
			}
		}
		ksort($data);
		return $data;
	}

	public function auth( $get_model = false )
	{
		if ($this->app->auth && $get_model) {
			$m = $this->app->auth->getModel();
			if ($m->loaded()) {
				return $m;
			}
		} elseif ($this->app->auth && !$get_model) {
			$d = $this->app->auth->getData();
			if ( !empty($d)) {
				return $d;
			}
		}
		throw new Exception('Unauthorized', 401);
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function param( string $name )
	{
		$params = $this->router->getParams();
		if (isset($params[$name])) {
			return $params[$name];
		}
		return null;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function body( string $key )
	{
		$body = $this->payload;
		if (isset($body[$key])) {
			return $body[$key];
		}
		return null;
	}

	public function getOne( array $data ): void
	{
		$this->res->status(200, 'OK')->json($data);
	}

	public function deleteOne(): void
	{
		$this->res->status(410, 'Gone');
	}

	public function createOne( array $data ): void
	{
		$this->res->status(201, 'Created')->json($data);
	}

	public function updateOne( array $data ): void
	{
		$this->res->status(200, 'OK')->json($data);
	}
}
