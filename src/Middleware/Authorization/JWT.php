<?php
declare(strict_types = 1);

namespace funyx\api\Middleware\Authorization;

use Firebase\JWT\JWT as FirebaseJWT;
use funyx\api\Exception;
use funyx\api\Middleware\Authorization;

class JWT extends Authorization
{
	protected function loadConfig():void
	{
		parent::loadConfig();
		if(!$this->config->get('jwt')){
			throw new Exception('Set `jwt` authorization configuration');
		}
		$this->config = $this->config->get('jwt');
	}
	public function authorize( $service_data = null ): void
	{
		$req = $this->application->getService('request');
		if ($req->hasHeader('Authorization')) {
			[
				$prefix,
				$token
			] = explode(' ', $req->getHeader('Authorization'));
			if ( !empty($token)) {
				try {
					$service_data = json_decode(json_encode(FirebaseJWT::decode(
						$token,
						$this->config->get('secret'),
						$this->config->get('algorithm')->getValues()
					)), true);
				} catch (\Exception $e) {
					throw new Exception($e->getMessage());
				}
			}
		}
		parent::authorize($service_data);
	}
}
