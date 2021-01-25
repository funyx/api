<?php
declare(strict_types=1);

namespace funyx\api\Data\AccountUser;

use Firebase\JWT\JWT;
use funyx\api\Data\Field\Password;
use funyx\api\Data\AccountUser;
use funyx\api\Exception;

class AccountUserAuth extends AccountUser
{
	public function init(): void
	{
		parent::init();

		$this->addField('password', [
			Password::class,
			'required' => true
		]);
	}

	public function register(): array
	{
		$this->save($this->request->getJsonRawBody(true));
		return $this->publicUserData();
	}

	public function login(): array
	{
		$req = $this->request->getJsonRawBody(true);
		$this->tryLoadByUsernameAndPassword($req['username'],$req['password']);
		if(!$this->loaded()){
			throw new Exception('Wrong credentials');
		}
		$user = $this->get();
		$token = $this->generateJWT($user);
		return $this->publicUserData([
			'access_token' => $token
		]);
	}

	protected function tryLoadByUsernameAndPassword($username, $password): AccountUserAuth
	{
		$this->unload();
		$this->addCondition('username', $username);
		if($this->tryLoadAny()->loaded()){
			$p = $this->data['password'];
			if(password_verify($password, $p)){
				return $this;
			}
		}
		return $this->unload();
	}

	public function getMe()
	{
		if($auth = $this->auth(true)){
			return $auth['model']->publicUserData();
		}
	}

	protected function generateJWT( array $user )
	{
		$cfg = $this->app->getSharedService('config')
		                 ->get('authorization')
		                 ->get('jwt');
		$token = [
			'id' => $user['id'],
			'username' => $user['username'],
			'iat' => time()
		];
		if($cfg->get('valid_for')){
			$token['exp'] = strtotime($cfg->get('valid_for'));
		}
		if($cfg->get('valid_after')){
			$token['nbf'] = strtotime($cfg->get('valid_after'));
		}
		return JWT::encode($token, $cfg->get('secret'));
	}
}
