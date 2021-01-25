<?php

namespace funyx\api;

use atk4\data\Persistence;
use DateTime;
use funyx\api\Data\AccountUser;
use Phalcon\Http\Request;
use Phalcon\Mvc\Router\RouteInterface;

class Model extends \atk4\data\Model
{
	public string $datetime_format = \DateTime::ATOM;
	/**
	 * @var \funyx\api\App
	 */
	protected App $app;
	protected RouteInterface $route;
	/**
	 * @var mixed
	 */
	protected Request $request;
	/**
	 * @var bool $has_created_at
	 */
	protected bool $has_created_at = false;
	/**
	 * @var bool $has_updated_at
	 */
	protected bool $has_updated_at = false;

	public function __construct( $persistence = null, $defaults = [] )
	{

		$this->app = $GLOBALS['app'];
		$this->route = $this->app->getRouter()->getMatchedRoute();
		$this->request = $this->app->getSharedService('request');

		$action = $this->route->getPaths()['action'];
		$class = $this->route->getPaths()['class'];
		if (get_class($this) === $class && !method_exists($this, $action)) {
			throw new Exception(get_class($this).' has no action '.$action);
		}
		if (is_null($this->persistence) && is_null($persistence)) {
			$this->persistence = Persistence::connect(getenv('DEFAULT_PERSISTENCE'));
			// init
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
		foreach ($this->getFields() as $f) {
			$value = $f->get();
			if (is_a($value, $f->dateTimeClass)) {
				$value = $value->format($this->datetime_format);
			}
			$data[$f->short_name] = $value;
		}
		return $data;
	}

	public function auth( $autoload_account_user_model = false )
	{
		$auth = $this->app->getSharedService('authorize');
		// TODO check token
		if ($autoload_account_user_model) {
			if(!isset($auth['model'])){
				$au = (new AccountUser($this->persistence))
					->tryLoad($auth['id']);
				if($au->loaded()){
					$auth['model'] = $au;
					// update the service
					$this->app->setService('authorize', function() use ($auth){
						return $auth;
					}, true);
				}
			}
		}
		return $auth;
	}
}
