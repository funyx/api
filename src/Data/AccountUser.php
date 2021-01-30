<?php
declare(strict_types=1);

namespace funyx\api\Data;

use funyx\api\Exception;
use funyx\api\Model;

class AccountUser extends Model
{
	public $table = 'account_user';
	public $caption = 'Account User';

	public array $data_map = [
		'username',
		'email',
		'created_at',
		'updated_at'
	];

	protected bool $has_created_at = true;
	protected bool $has_updated_at = true;

	protected function init(): void
	{
		parent::init();

		$this->addField('username',[
			'required' => true
		]);

		$this->addField('email', [
			'required' => true
		]);
		$this->onHook(self::HOOK_BEFORE_INSERT, function($m){
			// unique username
			$check = (clone $m)->unload();
			if ($check->tryLoadBy('username', $m->data['username'])->loaded()) {
				throw new Exception('Username is already taken');
			}
			// unique email
			$check = (clone $m)->unload();
			if ($check->tryLoadBy('email', $m->data['email'])->loaded()) {
				throw new Exception('Email is already registered');
			}
		});
	}

	public function publicUserData( array $extra = [] ): array
	{
		$user = $this->format();
		$user = array_merge_recursive($user, $extra);
		ksort($user, SORT_NATURAL);
		// all fields but id,pass
		unset($user['id']);
		unset($user['password']);
		return $user;
	}
}
