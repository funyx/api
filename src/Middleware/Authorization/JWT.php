<?php
declare(strict_types = 1);

namespace funyx\api\Middleware\Authorization;

use Firebase\JWT\JWT as FirebaseJWT;
use funyx\api\Data\AccountUser\AccountUserAuth;
use funyx\api\Exception;
use funyx\api\Middleware\Authorization;

class JWT extends Authorization
{
	public string $strategy_id = 'jwt';

	protected string $token;
	protected array $data = [];

	public function init(): void
	{
		if ($this->app->request->hasHeader('Authorization')) {
			[
				$prefix,
				$token
			] = explode(' ', $this->app->request->getHeader('Authorization'));
			if ( !empty($token)) {
				$this->token = $token;
				$this->data = json_decode(json_encode(FirebaseJWT::decode($token, $this->config->get('secret'),
					$this->config->get('algorithm')->getValues())), true);
			}
		}
		// TODO check query for access_token

		// TODO check body for access_token
	}

	public function useModel(AccountUserAuth &$m)
	{
		if(!empty($this->data) && isset($this->data['id'])){
			$m->tryLoad($this->data['id']);
		}
	}

	protected function setConfig(): void
	{
		parent::setConfig();
		if ( !$this->config->get('jwt')) {
			throw new Exception('Set `jwt` authorization configuration');
		}
		$this->config = $this->config->get('jwt');
	}
}
